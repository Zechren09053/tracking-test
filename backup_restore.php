<?php
class BackupRestore {
    private $dbHost;
    private $dbName;
    private $dbUser;
    private $dbPass;
    private $backupDir;
    private $websiteDir;
    private $excludePaths;
    
    public function __construct($dbHost, $dbName, $dbUser, $dbPass, $backupDir = 'backups/', $websiteDir = './') {
        $this->dbHost = $dbHost;
        $this->dbName = $dbName;
        $this->dbUser = $dbUser;
        $this->dbPass = $dbPass;
        $this->backupDir = rtrim($backupDir, '/') . '/';
        $this->websiteDir = rtrim($websiteDir, '/') . '/';
        
        // Default excluded paths (including the backups directory)
        $this->excludePaths = array($this->backupDir);
        
        // Always exclude the current script itself
        $currentScript = $_SERVER['SCRIPT_FILENAME'];
        if (file_exists($currentScript)) {
            $this->excludePaths[] = $currentScript;
        }
        
        // Also explicitly exclude backup_restore.php in the website directory
        $backupRestoreScript = $this->websiteDir . 'backup_restore.php';
        if (file_exists($backupRestoreScript)) {
            $this->excludePaths[] = $backupRestoreScript;
        }
        
        // Add .git directory to exclusions if it exists
        $gitDir = $this->websiteDir . '.git';
        if (is_dir($gitDir)) {
            $this->excludePaths[] = $gitDir;
        }
        
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
        
        // Backup files (excluding backup directory itself and .git directory)
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
        
        $this->copyDirectory($this->websiteDir, $targetDir, $this->excludePaths);
    }
    
    /**
     * Recursively copy directory
     */
    private function copyDirectory($source, $destination, $excludePaths = array()) {
        $source = realpath($source);
        
        if (!$source) {
            throw new Exception("Source directory does not exist: $source");
        }
        
        // Check if this directory should be excluded
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
            $filePath = $item->getPathname();
            $relativePath = str_replace($source . DIRECTORY_SEPARATOR, '', $filePath);
            $target = $destination . DIRECTORY_SEPARATOR . $relativePath;
            
            // Check if this is the current script or backup_restore.php
            if (basename($filePath) === 'backup_restore.php' || 
                $filePath === $_SERVER['SCRIPT_FILENAME']) {
                continue; // Skip this file when copying
            }
            
            // Skip excluded paths
            $skip = false;
            foreach ($excludePaths as $excludePath) {
                $excludePath = realpath($excludePath);
                if ($excludePath && strpos($filePath, $excludePath) === 0) {
                    $skip = true;
                    break;
                }
            }
            
            if ($skip) continue;
            
            if ($item->isDir()) {
                mkdir($target, 0755, true);
            } else {
                copy($filePath, $target);
            }
        }
    }
    
    /**
     * Create backup info file
     */
    private function createBackupInfo($backupPath, $timestamp) {
        $info = array(
            'created_at' => $timestamp,
            'database' => $this->dbName,
            'php_version' => PHP_VERSION,
            'server_software' => isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown',
            'website_dir' => $this->websiteDir,
            'excluded_paths' => array_map(function($path) {
                return realpath($path) ?: $path;
            }, $this->excludePaths)
        );
        
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
        $backups = array();
        
        if (!is_dir($this->backupDir)) {
            return $backups;
        }
        
        $files = glob($this->backupDir . '*.zip');
        
        if (!$files) {
            return $backups;
        }
        
        foreach ($files as $file) {
            $backups[] = array(
                'name' => basename($file, '.zip'),
                'file' => $file,
                'size' => filesize($file),
                'created' => date('Y-m-d H:i:s', filemtime($file))
            );
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
        $this->copyDirectory($this->websiteDir, $currentBackup, $this->excludePaths);
        
        try {
            // Make sure to save the script itself
            $scriptsToPreserve = array();
            
            // Get the current script
            $currentScript = $_SERVER['SCRIPT_FILENAME'];
            if (file_exists($currentScript)) {
                $scriptContent = file_get_contents($currentScript);
                $scriptsToPreserve[$currentScript] = $scriptContent;
            }
            
            // Also check for backup_restore.php in the website directory
            $backupRestoreScript = $this->websiteDir . 'backup_restore.php';
            if (file_exists($backupRestoreScript) && $backupRestoreScript !== $currentScript) {
                $scriptContent = file_get_contents($backupRestoreScript);
                $scriptsToPreserve[$backupRestoreScript] = $scriptContent;
            }
            
            // Clear current website directory (except backups and excluded paths)
            $this->clearDirectory($this->websiteDir, $this->excludePaths);
            
            // Restore files
            $this->copyDirectory($sourceDir, $this->websiteDir);
            
            // Restore preserved scripts
            foreach ($scriptsToPreserve as $scriptPath => $content) {
                file_put_contents($scriptPath, $content);
            }
            
        } catch (Exception $e) {
            // If restore fails, restore from current backup
            $this->clearDirectory($this->websiteDir, $this->excludePaths);
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
    private function clearDirectory($dir, $excludePaths = array()) {
        if (!is_dir($dir)) return;
        
        // Ensure the current script is in excluded paths
        $currentScript = $_SERVER['SCRIPT_FILENAME'];
        if (file_exists($currentScript) && !in_array($currentScript, $excludePaths)) {
            $excludePaths[] = $currentScript;
        }
        
        // Also explicitly exclude backup_restore.php in the website directory
        $backupRestoreScript = $this->websiteDir . 'backup_restore.php';
        if (file_exists($backupRestoreScript) && !in_array($backupRestoreScript, $excludePaths)) {
            $excludePaths[] = $backupRestoreScript;
        }
        
        $iterator = new DirectoryIterator($dir);
        
        foreach ($iterator as $file) {
            if ($file->isDot()) continue;
            
            $filePath = $file->getPathname();
            $realFilePath = realpath($filePath);
            
            // Check if this is the current script or backup_restore.php
            if ($realFilePath === realpath($currentScript) || 
                basename($filePath) === 'backup_restore.php') {
                continue; // Skip the current script file
            }
            
            $skip = false;
            
            foreach ($excludePaths as $excludePath) {
                $excludePath = realpath($excludePath);
                if ($excludePath && $realFilePath && strpos($realFilePath, $excludePath) === 0) {
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

// Handle AJAX requests first - IMPORTANT to prevent any output before headers
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Prevent PHP errors from being displayed in the response
    ini_set('display_errors', 0);
    error_reporting(0);
    
    // Set correct content type header
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
        
        $action = isset($_POST['action']) ? $_POST['action'] : '';
        
        switch ($action) {
            case 'create_backup':
                $backupName = isset($_POST['backup_name']) ? $_POST['backup_name'] : null;
                $backupFile = $backup->createFullBackup($backupName);
                echo json_encode(array('success' => true, 'backup_file' => basename($backupFile)));
                break;
                
            case 'list_backups':
                $backups = $backup->listBackups();
                echo json_encode(array('success' => true, 'backups' => $backups));
                break;
                
            case 'restore_backup':
                $backupName = isset($_POST['backup_name']) ? $_POST['backup_name'] : '';
                $restoreFiles = isset($_POST['restore_files']) && $_POST['restore_files'] === 'true' ? true : false;
                $restoreDatabase = isset($_POST['restore_database']) && $_POST['restore_database'] === 'true' ? true : false;
                
                $backupFile = 'backups/' . $backupName . '.zip';
                $backup->restore($backupFile, $restoreFiles, $restoreDatabase);
                echo json_encode(array('success' => true));
                break;
                
            case 'delete_backup':
                $backupName = isset($_POST['backup_name']) ? $_POST['backup_name'] : '';
                $backup->deleteBackup($backupName);
                echo json_encode(array('success' => true));
                break;
                
            default:
                throw new Exception('Invalid action');
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array('success' => false, 'error' => $e->getMessage()));
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
    <link rel="stylesheet" href="backup.css">
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
        // Global error handler to display JSON parsing errors
        window.addEventListener('error', function(event) {
            if (event.error instanceof SyntaxError && event.error.message.includes('JSON')) {
                showMessage('Error parsing server response. Check server logs for PHP errors.', 'error');
            }
        });
        
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
            
            fetch(window.location.href, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=create_backup&backup_name=${encodeURIComponent(backupName)}`
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`Server error: ${text}`);
                    });
                }
                return response.json();
            })
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
                console.error('Error details:', error);
            });
        }
        
        function loadBackups() {
            const loading = document.getElementById('listLoading');
            const backupsList = document.getElementById('backupsList');
            const restoreSelect = document.getElementById('restoreSelect');
            
            loading.style.display = 'block';
            
            fetch(window.location.href, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=list_backups'
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`Server error: ${text}`);
                    });
                }
                return response.json();
            })
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
                console.error('Error details:', error);
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
            
            fetch(window.location.href, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=restore_backup&backup_name=${encodeURIComponent(backupName)}&restore_files=${restoreFiles}&restore_database=${restoreDatabase}`
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`Server error: ${text}`);
                    });
                }
                return response.json();
            })
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
                console.error('Error details:', error);
            });
        }
        
        function deleteBackup(backupName) {
            if (!confirm(`Are you sure you want to delete backup "${backupName}"?`)) {
                return;
            }
            
            fetch(window.location.href, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=delete_backup&backup_name=${encodeURIComponent(backupName)}`
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`Server error: ${text}`);
                    });
                }
                return response.json();
            })
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
                console.error('Error details:', error);
            });
        }
        
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        // Hide loading indicators by default
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('createLoading').style.display = 'none';
            document.getElementById('restoreLoading').style.display = 'none';
            document.getElementById('listLoading').style.display = 'none';
            loadBackups();
        });
    </script>
</body>
</html>