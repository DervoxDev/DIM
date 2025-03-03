<?php

    namespace App\Filament\Resources;

    use App\Filament\Resources\TeamResource\Pages;
    use App\Filament\Resources\TeamResource\RelationManagers;
    use App\Models\Team;
    use App\Models\User;
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

            Forms\Components\Select::make('team_admin_id')
                                   ->label('Team Admin')
                                   ->options(User::role('team_admin')->pluck('name', 'id')->toArray())
                                   ->required()
                                   ->searchable()
                                   ->helperText('Select a team admin for this team'),

            Forms\Components\Select::make('plan_id')
                                   ->label('Subscription Type')
                                   ->options(\App\Models\Plan::where('is_active', true)->pluck('name', 'id')->toArray())
                                   ->required(),

            Forms\Components\DatePicker::make('subscription_expiredDate')
                                       ->label('Subscription Expiry Date')
                                       ->required(),

            Forms\Components\Select::make('subscription.status')
                                   ->label('Subscription Status')
                                   ->options([
                                       'active' => 'Active',
                                       'expired' => 'Expired',
                                       'cancelled' => 'Cancelled',
                                   ])
                                   ->required()
        ]);
        }


        public static function table(Table $table): Table
        {

            return $table
        ->columns([
            Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('users_count')->counts('users')->label('Users'),
            Tables\Columns\TextColumn::make('subscription.plan.name')
                                     ->label('Subscription Type')
                                     ->sortable()
                                     ->searchable(),
            Tables\Columns\TextColumn::make('subscription.subscription_expiredDate')
                                     ->label('Subscription Expiry')
                                     ->date()
                                     ->sortable()
                                     ->searchable(),
            Tables\Columns\TextColumn::make('subscription.status')
                                     ->label('Status')
                                     ->sortable()
                                     ->searchable(),
            /* Tables\Columns\TextColumn::make('created_at')
             *                          ->dateTime()
             *                          ->sortable()
             *                          ->searchable(), */
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
