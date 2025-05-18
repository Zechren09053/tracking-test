<?php
class BackupRestore {
    private $dbHost;
    private $dbName;
    private $dbUser;
    private $dbPass;
    private $backupDir;
    private $websiteDir;
    
    public function __construct($dbHost, $dbName, $dbUser, $dbPass, $backupDir = 'backups/', $websiteDir = './') {
        $this->dbHost = $dbHost;
        $this->dbName = $dbName;
        $this->dbUser = $dbUser;
        $this->dbPass = $dbPass;
        $this->backupDir = rtrim($backupDir, '/') . '/';
        $this->websiteDir = rtrim($websiteDir, '/') . '/';
        
        // Create backup directory if it doesn't exist
        if (!file_exists($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }
    
    /**
     * Create a full backup (database + files)
     */
    public function createFullBackup($backupName = null) {
        $timestamp = date('Y-m-d_H-i-s');
        $backupName = $backupName ?: "backup_$timestamp";
        $backupPath = $this->backupDir . $backupName;
        
        // Create backup directory
        if (!mkdir($backupPath, 0755, true)) {
            throw new Exception("Failed to create backup directory: $backupPath");
        }
        
        // Backup database
        $dbBackupFile = $backupPath . '/database.sql';
        $this->backupDatabase($dbBackupFile);
        
        // Backup files (excluding backup directory itself)
        $this->backupFiles($backupPath . '/files');
        
        // Create backup info file
        $this->createBackupInfo($backupPath, $timestamp);
        
        // Create compressed archive
        $zipFile = $backupPath . '.zip';
        $this->createZipArchive($backupPath, $zipFile);
        
        // Remove uncompressed backup directory
        $this->removeDirectory($backupPath);
        
        return $zipFile;
    }
    
    /**
     * Backup database to SQL file
     */
    private function backupDatabase($outputFile) {
        $mysqldumpPath = 'mysqldump'; // Adjust path if needed
        
        $command = sprintf(
            '%s --host=%s --user=%s --password=%s --single-transaction --routines --triggers %s > %s',
            $mysqldumpPath,
            escapeshellarg($this->dbHost),
            escapeshellarg($this->dbUser),
            escapeshellarg($this->dbPass),
            escapeshellarg($this->dbName),
            escapeshellarg($outputFile)
        );
        
        exec($command, $output, $returnVar);
        
        if ($returnVar !== 0) {
            // Fallback to PHP-based backup
            $this->backupDatabaseWithPHP($outputFile);
        }
    }
    
    /**
     * PHP-based database backup (fallback)
     */
    private function backupDatabaseWithPHP($outputFile) {
        try {
            $pdo = new PDO("mysql:host={$this->dbHost};dbname={$this->dbName}", $this->dbUser, $this->dbPass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sql = "-- Database Backup\n";
            $sql .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n\n";
            $sql .= "SET FOREIGN_KEY_CHECKS=0;\n";
            
            // Get all tables
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($tables as $table) {
                // Get table structure
                $sql .= "\n-- Table structure for `$table`\n";
                $sql .= "DROP TABLE IF EXISTS `$table`;\n";
                
                $createStmt = $pdo->query("SHOW CREATE TABLE `$table`");
                $createTable = $createStmt->fetch(PDO::FETCH_ASSOC);
                $sql .= $createTable['Create Table'] . ";\n\n";
                
                // Get table data
                $sql .= "-- Dumping data for table `$table`\n";
                $dataStmt = $pdo->query("SELECT * FROM `$table`");
                
                while ($row = $dataStmt->fetch(PDO::FETCH_ASSOC)) {
                    $values = array_map(function($value) use ($pdo) {
                        return $value === null ? 'NULL' : $pdo->quote($value);
                    }, $row);
                    
                    $sql .= "INSERT INTO `$table` VALUES (" . implode(', ', $values) . ");\n";
                }
                $sql .= "\n";
            }
            
            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
            
            file_put_contents($outputFile, $sql);
            
        } catch (PDOException $e) {
            throw new Exception("Database backup failed: " . $e->getMessage());
        }
    }
    
    /**
     * Backup files
     */
    private function backupFiles($targetDir) {
        if (!mkdir($targetDir, 0755, true)) {
            throw new Exception("Failed to create files backup directory: $targetDir");
        }
        
        $this->copyDirectory($this->websiteDir, $targetDir, [$this->backupDir]);
    }
    
    /**
     * Recursively copy directory
     */
    private function copyDirectory($source, $destination, $excludePaths = []) {
        $source = realpath($source);
        
        foreach ($excludePaths as $excludePath) {
            $excludePath = realpath($excludePath);
            if ($excludePath && strpos($source, $excludePath) === 0) {
                return; // Skip excluded paths
            }
        }
        
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $item) {
            $relativePath = str_replace($source . DIRECTORY_SEPARATOR, '', $item->getPathname());
            $target = $destination . DIRECTORY_SEPARATOR . $relativePath;
            
            // Skip excluded paths
            $skip = false;
            foreach ($excludePaths as $excludePath) {
                $excludePath = realpath($excludePath);
                if ($excludePath && strpos($item->getPathname(), $excludePath) === 0) {
                    $skip = true;
                    break;
                }
            }
            
            if ($skip) continue;
            
            if ($item->isDir()) {
                mkdir($target, 0755, true);
            } else {
                copy($item->getPathname(), $target);
            }
        }
    }
    
    /**
     * Create backup info file
     */
    private function createBackupInfo($backupPath, $timestamp) {
        $info = [
            'created_at' => $timestamp,
            'database' => $this->dbName,
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'website_dir' => $this->websiteDir
        ];
        
        file_put_contents($backupPath . '/backup_info.json', json_encode($info, JSON_PRETTY_PRINT));
    }
    
    /**
     * Create ZIP archive
     */
    private function createZipArchive($sourceDir, $zipFile) {
        $zip = new ZipArchive();
        
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            throw new Exception("Cannot create zip file: $zipFile");
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            $relativePath = str_replace($sourceDir . DIRECTORY_SEPARATOR, '', $file->getPathname());
            
            if ($file->isDir()) {
                $zip->addEmptyDir($relativePath);
            } else {
                $zip->addFile($file->getPathname(), $relativePath);
            }
        }
        
        $zip->close();
    }
    
    /**
     * Remove directory recursively
     */
    private function removeDirectory($dir) {
        if (!is_dir($dir)) return;
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }
        
        rmdir($dir);
    }
    
    /**
     * List available backups
     */
    public function listBackups() {
        $backups = [];
        $files = glob($this->backupDir . '*.zip');
        
        foreach ($files as $file) {
            $backups[] = [
                'name' => basename($file, '.zip'),
                'file' => $file,
                'size' => filesize($file),
                'created' => date('Y-m-d H:i:s', filemtime($file))
            ];
        }
        
        return $backups;
    }
    
    /**
     * Restore from backup
     */
    public function restore($backupFile, $restoreFiles = true, $restoreDatabase = true) {
        if (!file_exists($backupFile)) {
            throw new Exception("Backup file not found: $backupFile");
        }
        
        $tempDir = sys_get_temp_dir() . '/restore_' . uniqid();
        
        // Extract backup
        $zip = new ZipArchive();
        if ($zip->open($backupFile) !== TRUE) {
            throw new Exception("Cannot open backup file: $backupFile");
        }
        
        $zip->extractTo($tempDir);
        $zip->close();
        
        try {
            // Restore database
            if ($restoreDatabase && file_exists($tempDir . '/database.sql')) {
                $this->restoreDatabase($tempDir . '/database.sql');
            }
            
            // Restore files
            if ($restoreFiles && is_dir($tempDir . '/files')) {
                $this->restoreFiles($tempDir . '/files');
            }
            
        } finally {
            // Clean up
            $this->removeDirectory($tempDir);
        }
    }
    
    /**
     * Restore database from SQL file
     */
    private function restoreDatabase($sqlFile) {
        $mysql = 'mysql'; // Adjust path if needed
        
        $command = sprintf(
            '%s --host=%s --user=%s --password=%s %s < %s',
            $mysql,
            escapeshellarg($this->dbHost),
            escapeshellarg($this->dbUser),
            escapeshellarg($this->dbPass),
            escapeshellarg($this->dbName),
            escapeshellarg($sqlFile)
        );
        
        exec($command, $output, $returnVar);
        
        if ($returnVar !== 0) {
            // Fallback to PHP-based restore
            $this->restoreDatabaseWithPHP($sqlFile);
        }
    }
    
    /**
     * PHP-based database restore (fallback)
     */
    private function restoreDatabaseWithPHP($sqlFile) {
        try {
            $pdo = new PDO("mysql:host={$this->dbHost};dbname={$this->dbName}", $this->dbUser, $this->dbPass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sql = file_get_contents($sqlFile);
            $statements = preg_split('/;\s*\n/', $sql);
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    $pdo->exec($statement);
                }
            }
            
        } catch (PDOException $e) {
            throw new Exception("Database restore failed: " . $e->getMessage());
        }
    }
    
    /**
     * Restore files
     */
    private function restoreFiles($sourceDir) {
        // Create backup of current files before restore
        $currentBackup = $this->websiteDir . '../current_backup_' . date('Y-m-d_H-i-s');
        $this->copyDirectory($this->websiteDir, $currentBackup, [$this->backupDir]);
        
        try {
            // Clear current website directory (except backups)
            $this->clearDirectory($this->websiteDir, [$this->backupDir]);
            
            // Restore files
            $this->copyDirectory($sourceDir, $this->websiteDir);
            
        } catch (Exception $e) {
            // If restore fails, restore from current backup
            $this->clearDirectory($this->websiteDir, [$this->backupDir]);
            $this->copyDirectory($currentBackup, $this->websiteDir);
            $this->removeDirectory($currentBackup);
            throw $e;
        }
        
        // Clean up current backup
        $this->removeDirectory($currentBackup);
    }
    
    /**
     * Clear directory but preserve excluded paths
     */
    private function clearDirectory($dir, $excludePaths = []) {
        if (!is_dir($dir)) return;
        
        $iterator = new DirectoryIterator($dir);
        
        foreach ($iterator as $file) {
            if ($file->isDot()) continue;
            
            $filePath = $file->getPathname();
            $skip = false;
            
            foreach ($excludePaths as $excludePath) {
                $excludePath = realpath($excludePath);
                if ($excludePath && strpos(realpath($filePath), $excludePath) === 0) {
                    $skip = true;
                    break;
                }
            }
            
            if ($skip) continue;
            
            if ($file->isDir()) {
                $this->removeDirectory($filePath);
            } else {
                unlink($filePath);
            }
        }
    }
    
    /**
     * Delete a backup
     */
    public function deleteBackup($backupName) {
        $backupFile = $this->backupDir . $backupName . '.zip';
        
        if (!file_exists($backupFile)) {
            throw new Exception("Backup not found: $backupName");
        }
        
        return unlink($backupFile);
    }
}

// Usage example and web interface
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle AJAX requests
    header('Content-Type: application/json');
    
    try {
        // Initialize backup system
        $backup = new BackupRestore(
            'localhost',    // DB host
            'prfs',         // DB name
            'PRFS',         // DB user
            '1111',         // DB password
            'backups/',     // Backup directory
            './'            // Website directory
        );
        
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'create_backup':
                $backupName = $_POST['backup_name'] ?? null;
                $backupFile = $backup->createFullBackup($backupName);
                echo json_encode(['success' => true, 'backup_file' => basename($backupFile)]);
                break;
                
            case 'list_backups':
                $backups = $backup->listBackups();
                echo json_encode(['success' => true, 'backups' => $backups]);
                break;
                
            case 'restore_backup':
                $backupName = $_POST['backup_name'] ?? '';
                $restoreFiles = $_POST['restore_files'] ?? true;
                $restoreDatabase = $_POST['restore_database'] ?? true;
                
                $backupFile = 'backups/' . $backupName . '.zip';
                $backup->restore($backupFile, $restoreFiles, $restoreDatabase);
                echo json_encode(['success' => true]);
                break;
                
            case 'delete_backup':
                $backupName = $_POST['backup_name'] ?? '';
                $backup->deleteBackup($backupName);
                echo json_encode(['success' => true]);
                break;
                
            default:
                throw new Exception('Invalid action');
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Backup & Restore</title>
    <style>
    /* Dark Theme CSS for Backup & Restore Interface */
body { 
    font-family: Arial, sans-serif; 
    margin: 20px; 
    background-color: #1a1a1a;
    color: #e0e0e0;
}

.container { 
    max-width: 800px; 
    margin: 0 auto; 
}

.section { 
    margin-bottom: 30px; 
    padding: 20px; 
    border: 1px solid #444; 
    border-radius: 5px; 
    background-color: #2d2d2d;
}

.backup-item { 
    padding: 10px; 
    border-bottom: 1px solid #444; 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    background-color: #333;
    margin-bottom: 5px;
    border-radius: 3px;
}

.backup-item:last-child {
    border-bottom: none;
}

.btn { 
    padding: 10px 15px; 
    margin: 5px; 
    border: none; 
    border-radius: 3px; 
    cursor: pointer; 
    transition: all 0.2s ease;
}

.btn-primary { 
    background: #0d6efd; 
    color: white; 
}

.btn-primary:hover {
    background: #0b5ed7;
}

.btn-success { 
    background: #198754; 
    color: white; 
}

.btn-success:hover {
    background: #157347;
}

.btn-danger { 
    background: #dc3545; 
    color: white; 
}

.btn-danger:hover {
    background: #bb2d3b;
}

.btn-warning { 
    background: #fd7e14; 
    color: white; 
}

.btn-warning:hover {
    background: #e8680f;
}

.btn:hover { 
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

input[type="text"], select { 
    padding: 8px; 
    margin: 5px; 
    border: 1px solid #555; 
    border-radius: 3px; 
    background-color: #3a3a3a;
    color: #e0e0e0;
}

input[type="text"]:focus, select:focus {
    outline: none;
    border-color: #0d6efd;
    box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.25);
}

.loading { 
    display: none; 
    color: #ffc107;
    font-style: italic;
}

.message { 
    padding: 10px; 
    margin: 10px 0; 
    border-radius: 3px; 
}

.success { 
    background: #0f2419; 
    border: 1px solid #1d4326; 
    color: #8fd19e; 
}

.error { 
    background: #2c0e0f; 
    border: 1px solid #5a1a1e; 
    color: #f1aeb5; 
}

.checkbox-group { 
    margin: 10px 0; 
}

.checkbox-group label { 
    margin-left: 5px; 
    color: #e0e0e0;
}

.checkbox-group input[type="checkbox"] {
    accent-color: #0d6efd;
}

/* Header styling */
h1 {
    color: #ffffff;
    text-align: center;
    margin-bottom: 30px;
    border-bottom: 2px solid #444;
    padding-bottom: 10px;
}

h2 {
    color: #ffffff;
    margin-top: 0;
    margin-bottom: 20px;
}

/* Scrollbar styling for webkit browsers */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #2d2d2d;
}

::-webkit-scrollbar-thumb {
    background: #555;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #666;
}

/* Additional dark theme improvements */
option {
    background-color: #3a3a3a;
    color: #e0e0e0;
}

/* Backup item text styling */
.backup-item strong {
    color: #ffffff;
}

.backup-item small {
    color: #aaa;
}

/* Loading animation */
.loading::after {
    content: '';
    display: inline-block;
    width: 12px;
    height: 12px;
    margin-left: 8px;
    border: 2px solid #666;
    border-radius: 50%;
    border-top-color: #ffc107;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
    </style>
</head>
<body>
    <div class="container">
        <h1>Website Backup & Restore</h1>
        
        <div id="message"></div>
        
        <!-- Create Backup Section -->
        <div class="section">
            <h2>Create Backup</h2>
            <input type="text" id="backupName" placeholder="Backup name (optional)">
            <button class="btn btn-primary" onclick="createBackup()">Create Full Backup</button>
            <div id="createLoading" class="loading">Creating backup...</div>
        </div>
        
        <!-- Restore Section -->
        <div class="section">
            <h2>Restore from Backup</h2>
            <select id="restoreSelect">
                <option value="">Select a backup...</option>
            </select>
            <div class="checkbox-group">
                <input type="checkbox" id="restoreFiles" checked>
                <label for="restoreFiles">Restore Files</label>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" id="restoreDatabase" checked>
                <label for="restoreDatabase">Restore Database</label>
            </div>
            <button class="btn btn-warning" onclick="restoreBackup()">Restore Selected Backup</button>
            <div id="restoreLoading" class="loading">Restoring backup...</div>
        </div>
        
        <!-- Backup List Section -->
        <div class="section">
            <h2>Available Backups</h2>
            <button class="btn btn-success" onclick="loadBackups()">Refresh List</button>
            <div id="backupsList"></div>
            <div id="listLoading" class="loading">Loading backups...</div>
        </div>
    </div>

    <script>
        function showMessage(message, type = 'success') {
            const messageDiv = document.getElementById('message');
            messageDiv.className = `message ${type}`;
            messageDiv.textContent = message;
            setTimeout(() => messageDiv.textContent = '', 5000);
        }
        
        function createBackup() {
            const backupName = document.getElementById('backupName').value;
            const loading = document.getElementById('createLoading');
            
            loading.style.display = 'block';
            
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=create_backup&backup_name=${encodeURIComponent(backupName)}`
            })
            .then(response => response.json())
            .then(data => {
                loading.style.display = 'none';
                if (data.success) {
                    showMessage(`Backup created successfully: ${data.backup_file}`, 'success');
                    document.getElementById('backupName').value = '';
                    loadBackups();
                } else {
                    showMessage(`Error: ${data.error}`, 'error');
                }
            })
            .catch(error => {
                loading.style.display = 'none';
                showMessage(`Error: ${error.message}`, 'error');
            });
        }
        
        function loadBackups() {
            const loading = document.getElementById('listLoading');
            const backupsList = document.getElementById('backupsList');
            const restoreSelect = document.getElementById('restoreSelect');
            
            loading.style.display = 'block';
            
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=list_backups'
            })
            .then(response => response.json())
            .then(data => {
                loading.style.display = 'none';
                if (data.success) {
                    // Update backups list
                    backupsList.innerHTML = '';
                    restoreSelect.innerHTML = '<option value="">Select a backup...</option>';
                    
                    data.backups.forEach(backup => {
                        // Add to list display
                        const item = document.createElement('div');
                        item.className = 'backup-item';
                        item.innerHTML = `
                            <div>
                                <strong>${backup.name}</strong><br>
                                <small>Created: ${backup.created} | Size: ${formatFileSize(backup.size)}</small>
                            </div>
                            <div>
                                <button class="btn btn-danger" onclick="deleteBackup('${backup.name}')">Delete</button>
                            </div>
                        `;
                        backupsList.appendChild(item);
                        
                        // Add to restore select
                        const option = document.createElement('option');
                        option.value = backup.name;
                        option.textContent = `${backup.name} (${backup.created})`;
                        restoreSelect.appendChild(option);
                    });
                    
                    if (data.backups.length === 0) {
                        backupsList.innerHTML = '<p>No backups found.</p>';
                    }
                } else {
                    showMessage(`Error: ${data.error}`, 'error');
                }
            })
            .catch(error => {
                loading.style.display = 'none';
                showMessage(`Error: ${error.message}`, 'error');
            });
        }
        
        function restoreBackup() {
            const backupName = document.getElementById('restoreSelect').value;
            const restoreFiles = document.getElementById('restoreFiles').checked;
            const restoreDatabase = document.getElementById('restoreDatabase').checked;
            const loading = document.getElementById('restoreLoading');
            
            if (!backupName) {
                showMessage('Please select a backup to restore.', 'error');
                return;
            }
            
            if (!confirm('Are you sure you want to restore this backup? This will overwrite current data.')) {
                return;
            }
            
            loading.style.display = 'block';
            
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=restore_backup&backup_name=${encodeURIComponent(backupName)}&restore_files=${restoreFiles}&restore_database=${restoreDatabase}`
            })
            .then(response => response.json())
            .then(data => {
                loading.style.display = 'none';
                if (data.success) {
                    showMessage('Backup restored successfully!', 'success');
                } else {
                    showMessage(`Error: ${data.error}`, 'error');
                }
            })
            .catch(error => {
                loading.style.display = 'none';
                showMessage(`Error: ${error.message}`, 'error');
            });
        }
        
        function deleteBackup(backupName) {
            if (!confirm(`Are you sure you want to delete backup "${backupName}"?`)) {
                return;
            }
            
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=delete_backup&backup_name=${encodeURIComponent(backupName)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('Backup deleted successfully.', 'success');
                    loadBackups();
                } else {
                    showMessage(`Error: ${data.error}`, 'error');
                }
            })
            .catch(error => {
                showMessage(`Error: ${error.message}`, 'error');
            });
        }
        
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        // Load backups on page load
        document.addEventListener('DOMContentLoaded', loadBackups);
    </script>
</body>
</html>