    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        /**
         * Run the migrations.
         */
        public function up(): void
        {
            Schema::create('transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('wallet_id');
                $table->enum('action', ['Withdraw', 'deposite']);
                $table->text('note')->nullable();
                $table->decimal('amount', 20, 18);
                $table->string('address');
                $table->enum('state', ['pending', 'approved', 'denied'])->default('pending');

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('cascade');
                
                $table->timestamps();
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('transactions');
        }
    };
