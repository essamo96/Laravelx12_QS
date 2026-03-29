<?php

namespace App\Services\AdminGenerator;

use Illuminate\Support\Facades\File;

class TranslationService
{
    public function update(string $tableName, array $translations): void
    {
        $langDirAr = base_path('lang/ar');
        $langDirEn = base_path('lang/en');
        File::ensureDirectoryExists($langDirAr);
        File::ensureDirectoryExists($langDirEn);

        $arFile = $langDirAr . '/app.php';
        $enFile = $langDirEn . '/app.php';
        $arData = File::exists($arFile) ? include $arFile : [];
        $enData = File::exists($enFile) ? include $enFile : [];

        foreach ($translations as $key => $value) {
            $arData[$key] = $value['ar'] ?? $value['en'] ?? $key;
            $enData[$key] = $value['en'] ?? $key;
        }

        $this->writePhpArray($arFile, $arData);
        $this->writePhpArray($enFile, $enData);
    }

    private function writePhpArray(string $path, array $data): void
    {
        $export = var_export($data, true);
        $content = "<?php\n\nreturn {$export};\n";
        File::put($path, $content);
    }
}

