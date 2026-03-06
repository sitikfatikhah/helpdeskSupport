<?php

namespace App\Filament\Tables\Actions;

use App\Filament\Imports\AssetImporter;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Table;

public function table(Table $table): Table
{
    return $table
        ->headerActions([
            ImportAction::make()
                ->importer(AssetImporter::class)
        ]);
}

