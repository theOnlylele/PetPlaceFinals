<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vaccines', function (Blueprint $table) {
            $table->id();
            $table->string('vaccine_key')->unique();
            $table->string('name');
            $table->string('animal_type');
            $table->enum('pricing_method', ['fixed', 'per_dose'])->default('fixed');
            $table->decimal('fixed_price', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('vaccine_doses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vaccine_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('dose_number');
            $table->decimal('price', 10, 2);
            $table->timestamps();
            $table->unique(['vaccine_id', 'dose_number']);
        });

        $defaults = [
            'dog' => ['dog_rabies'=>'Anti-rabies','dog_cdv'=>'Canine Distemper Virus (CDV)','dog_adenovirus'=>'Canine Adenovirus Type 1 & 2','dog_parvovirus'=>'Canine Parvovirus (CPV)','dog_parainfluenza'=>'Canine Parainfluenza','dog_leptospirosis'=>'Leptospirosis','dog_lyme'=>'Lyme Disease','dog_influenza'=>'Canine Influenza (Dog Flu)','dog_coronavirus'=>'Canine Coronavirus','dog_bordetella'=>'Bordetella bronchiseptica'],
            'cat' => ['cat_fvrcp'=>'FVRCP (3-in-1 Combo Vaccine)','cat_rhinotracheitis'=>'Feline Viral Rhinotracheitis','cat_calicivirus'=>'Feline Calicivirus','cat_panleukopenia'=>'Feline Panleukopenia','cat_rabies'=>'Anti-rabies','cat_felv'=>'Feline Leukemia Virus (FeLV)','cat_fiv'=>'Feline Immunodeficiency Virus (FIV)','cat_bordetella'=>'Bordetella bronchiseptica','cat_fip'=>'Feline Infectious Peritonitis (FIP)'],
        ];
        foreach ($defaults as $animal => $items) {
            foreach ($items as $key => $name) {
                DB::table('vaccines')->insert([
                    'vaccine_key' => $key, 'name' => $name, 'animal_type' => $animal,
                    'pricing_method' => 'fixed', 'fixed_price' => 0, 'is_active' => true,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vaccine_doses');
        Schema::dropIfExists('vaccines');
    }
};
