

cd /var/www/laravel-test && php artisan tinker --execute="use App\Models\User; use Illuminate\Support\Facades\Hash; \$user = User::where('email', 'manager@example.com')->first(); \$user->password = Hash::make('PassPass1'); \$user->save(); echo 'Пароль изменен для: ' . \$user->email;"

