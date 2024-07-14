<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeamResource\Pages;
use App\Filament\Resources\TeamResource\RelationManagers;
use App\Models\Team;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\Pages\Page;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Model;
class TeamResource extends Resource
{
    protected static ?string $model = Team::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('subscription_type')
                    ->label('Subscription Type')
                    ->options([
                        'Basic' => 'Basic',
                        'Premium' => 'Premium',
                        'Enterprise' => 'Enterprise',
                    ])
                    ->required()
                    ->afterStateHydrated(function (Get $get, Set $set, ?Model $record) {
                        $set('subscription_type', $record?->subscription?->subscription_type);
                    }),
                Forms\Components\DatePicker::make('subscription_expiredDate')
                    ->label('Subscription Expiry Date')
                    ->required()
                    ->afterStateHydrated(function (Get $get, Set $set, ?Model $record) {
                        $set('subscription_expiredDate', $record?->subscription?->subscription_expiredDate);
                    }),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('users_count')->counts('users')->label('Users'),
                Tables\Columns\SelectColumn::make('subscription.subscription_type')
                    ->label('Subscription Type')
                    ->options([
                        'Basic' => 'Basic',
                        'Premium' => 'Premium',
                        'Enterprise' => 'Enterprise',
                    ])
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('subscription.subscription_expiredDate')
                    ->label('Subscription Expiry')
                    ->date()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

public static function getRelations(): array
{
    return [
        //
    ];
}

public static function getRecordSubNavigation(Page $page): array
{
    return $page->generateNavigationItems([
        Pages\ViewTeam::class,
        Pages\ViewTeamWorkers::class,
    ]);
}
public static function getPages(): array
{
    return [
        'index' => Pages\ListTeams::route('/'),
        'create' => Pages\CreateTeam::route('/create'),
        'view' => Pages\ViewTeam::route('/{record}'),
        'edit' => Pages\EditTeam::route('/{record}/edit'),
        'viewTeamWorkers' => Pages\ViewTeamWorkers::route('/{record}/workers'),
    ];
}
}
