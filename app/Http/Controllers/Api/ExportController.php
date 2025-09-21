<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use ZipArchive;
use Illuminate\Support\Facades\Storage;

class ExportController extends Controller
{
    /**
     * Export tasks data.
     */
    public function tasks(Request $request): Response
    {
        $user = Auth::user();
        $format = $request->get('format', 'json'); // json, csv, pdf
        
        $tasks = $user->tasks()->with(['category', 'attachments'])->get();
        
        return $this->exportData($tasks, 'tasks', $format);
    }
    
    /**
     * Export goals data.
     */
    public function goals(Request $request): Response
    {
        $user = Auth::user();
        $format = $request->get('format', 'json');
        
        $goals = $user->goals()->with(['category'])->get();
        
        return $this->exportData($goals, 'goals', $format);
    }
    
    /**
     * Export habits data.
     */
    public function habits(Request $request): Response
    {
        $user = Auth::user();
        $format = $request->get('format', 'json');
        
        $habits = $user->habits()->with(['habitLogs'])->get();
        
        return $this->exportData($habits, 'habits', $format);
    }
    
    /**
     * Export notes data.
     */
    public function notes(Request $request): Response
    {
        $user = Auth::user();
        $format = $request->get('format', 'json');
        
        $notes = $user->notes()->with(['attachments'])->get();
        
        return $this->exportData($notes, 'notes', $format);
    }
    
    /**
     * Export calendar data.
     */
    public function calendar(Request $request): Response
    {
        $user = Auth::user();
        $format = $request->get('format', 'json');
        
        $events = $user->calendarEvents()->with(['task', 'eventInstances'])->get();
        
        return $this->exportData($events, 'calendar', $format);
    }
    
    /**
     * Export all user data.
     */
    public function allData(Request $request): Response
    {
        $user = Auth::user();
        $format = $request->get('format', 'json');
        
        $data = [
            'user' => $user->only(['name', 'email', 'created_at']),
            'tasks' => $user->tasks()->with(['category', 'attachments'])->get(),
            'goals' => $user->goals()->with(['category'])->get(),
            'habits' => $user->habits()->with(['habitLogs'])->get(),
            'notes' => $user->notes()->with(['attachments'])->get(),
            'calendar_events' => $user->calendarEvents()->with(['task', 'eventInstances'])->get(),
            'categories' => $user->categories()->get(),
            'preferences' => $user->preferences,
            'export_date' => now()->toISOString(),
        ];
        
        return $this->exportData($data, 'complete_backup', $format);
    }
    
    /**
     * Create a comprehensive backup.
     */
    public function createBackup(Request $request): JsonResponse
    {
        $user = Auth::user();
        $includeAttachments = $request->get('include_attachments', false);
        
        try {
            // Create temporary directory for backup
            $backupDir = storage_path('app/temp/backup_' . $user->id . '_' . time());
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            
            // Export all data as JSON
            $data = [
                'user' => $user->only(['name', 'email', 'created_at']),
                'tasks' => $user->tasks()->with(['category', 'attachments'])->get(),
                'goals' => $user->goals()->with(['category'])->get(),
                'habits' => $user->habits()->with(['habitLogs'])->get(),
                'notes' => $user->notes()->with(['attachments'])->get(),
                'calendar_events' => $user->calendarEvents()->with(['task', 'eventInstances'])->get(),
                'categories' => $user->categories()->get(),
                'preferences' => $user->preferences,
                'backup_date' => now()->toISOString(),
                'app_version' => config('app.version', '1.0.0'),
            ];
            
            // Save data as JSON
            file_put_contents($backupDir . '/data.json', json_encode($data, JSON_PRETTY_PRINT));
            
            // Create README file
            $readme = "Life Atlas Organizer - Data Backup\n";
            $readme .= "====================================\n\n";
            $readme .= "Backup created: " . now()->format('Y-m-d H:i:s') . "\n";
            $readme .= "User: " . $user->name . " (" . $user->email . ")\n";
            $readme .= "Total tasks: " . $data['tasks']->count() . "\n";
            $readme .= "Total goals: " . $data['goals']->count() . "\n";
            $readme .= "Total habits: " . $data['habits']->count() . "\n";
            $readme .= "Total notes: " . $data['notes']->count() . "\n";
            $readme .= "Total events: " . $data['calendar_events']->count() . "\n\n";
            $readme .= "Files included:\n";
            $readme .= "- data.json: Complete data export\n";
            if ($includeAttachments) {
                $readme .= "- attachments/: All file attachments\n";
            }
            $readme .= "\nTo restore this backup, use the import feature in Life Atlas Organizer.\n";
            
            file_put_contents($backupDir . '/README.txt', $readme);
            
            // Copy attachments if requested
            if ($includeAttachments) {
                $attachmentsDir = $backupDir . '/attachments';
                mkdir($attachmentsDir, 0755, true);
                
                $attachments = $user->attachments()->get();
                foreach ($attachments as $attachment) {
                    if (Storage::exists($attachment->file_path)) {
                        $sourcePath = Storage::path($attachment->file_path);
                        $destPath = $attachmentsDir . '/' . basename($attachment->file_path);
                        copy($sourcePath, $destPath);
                    }
                }
            }
            
            // Create ZIP file
            $zipPath = storage_path('app/temp/backup_' . $user->name . '_' . now()->format('Y-m-d_H-i-s') . '.zip');
            $zip = new ZipArchive();
            
            if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
                // Add all files from backup directory
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($backupDir),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                );
                
                foreach ($files as $file) {
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = substr($filePath, strlen($backupDir) + 1);
                        $zip->addFile($filePath, $relativePath);
                    }
                }
                
                $zip->close();
                
                // Clean up temporary directory
                $this->deleteDirectory($backupDir);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Backup created successfully',
                    'download_url' => route('download.backup', ['file' => basename($zipPath)]),
                    'file_size' => $this->formatBytes(filesize($zipPath)),
                    'items_count' => [
                        'tasks' => $data['tasks']->count(),
                        'goals' => $data['goals']->count(),
                        'habits' => $data['habits']->count(),
                        'notes' => $data['notes']->count(),
                        'events' => $data['calendar_events']->count(),
                    ]
                ]);
                
            } else {
                throw new \Exception('Could not create ZIP file');
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create backup: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Export data in the specified format.
     */
    private function exportData($data, string $filename, string $format): Response
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = $filename . '_' . $timestamp;
        
        switch ($format) {
            case 'csv':
                return $this->exportToCsv($data, $filename);
            case 'pdf':
                return $this->exportToPdf($data, $filename);
            default:
                return $this->exportToJson($data, $filename);
        }
    }
    
    /**
     * Export data to JSON format.
     */
    private function exportToJson($data, string $filename): Response
    {
        $content = json_encode($data, JSON_PRETTY_PRINT);
        
        return response($content)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '.json"');
    }
    
    /**
     * Export data to CSV format.
     */
    private function exportToCsv($data, string $filename): Response
    {
        $csv = '';
        
        if (is_array($data) && isset($data[0])) {
            // If it's a collection/array of objects
            $headers = array_keys($data[0]->toArray());
            $csv .= implode(',', $headers) . "\n";
            
            foreach ($data as $item) {
                $row = $item->toArray();
                $csv .= implode(',', array_map(function($value) {
                    // Escape CSV values
                    if (is_array($value) || is_object($value)) {
                        $value = json_encode($value);
                    }
                    return '"' . str_replace('"', '""', $value) . '"';
                }, $row)) . "\n";
            }
        } else {
            // Handle single object or complex data
            $csv = "Data exported as JSON in CSV format\n";
            $csv .= '"' . str_replace('"', '""', json_encode($data, JSON_PRETTY_PRINT)) . '"';
        }
        
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '.csv"');
    }
    
    /**
     * Export data to PDF format using DomPDF.
     */
    private function exportToPdf($data, string $filename): Response
    {
        $html = view('exports.pdf', ['data' => $data, 'filename' => $filename])->render();
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
        
        return $pdf->download($filename . '.pdf');
    }
    
    /**
     * Recursively delete directory.
     */
    private function deleteDirectory(string $dir): void
    {
        if (!file_exists($dir)) return;
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
    
    /**
     * Format bytes to human readable format.
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}