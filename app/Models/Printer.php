<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Printer extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    // Helper: Discover Windows Printers
    public static function getSystemPrinters(): array
    {
        try {
            // Windows PowerShell command to list printers
            $command = 'powershell -Command "Get-Printer | Select-Object Name | ConvertTo-Json"';
            $output = shell_exec($command);
            $printers = json_decode($output, true);
            
            if (is_array($printers)) {
                // Handle single result vs multiple results from PowerShell JSON
                if (isset($printers['Name'])) {
                    return [$printers['Name'] => $printers['Name']];
                }
                $list = [];
                foreach ($printers as $p) {
                    $list[$p['Name']] = $p['Name'];
                }
                return $list;
            }
            return [];
        } catch (\Exception $e) {
            return [];
        }
    }
}
