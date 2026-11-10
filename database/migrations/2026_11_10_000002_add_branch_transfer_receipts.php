<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transfers', function (Blueprint $table) {
            $table->string('receipt_status', 24)->nullable();
            $table->unsignedInteger('receipt_version')->default(0);
            $table->integer('dispatched_by')->nullable();
            $table->timestamp('dispatched_at')->nullable();
        });

        Schema::table('transfer_details', function (Blueprint $table) {
            $table->decimal('dispatched_stock_quantity', 12, 3)->nullable();
            $table->decimal('received_quantity', 12, 3)->default(0);
        });

        Schema::create('transfer_receipts', function (Blueprint $table) {
            $table->id();
            $table->integer('transfer_id');
            $table->integer('user_id');
            $table->uuid('request_id');
            $table->string('action', 16);
            $table->text('notes')->nullable();
            $table->json('lines');
            $table->timestamp('created_at');
            $table->unique(['transfer_id', 'request_id']);
            $table->foreign('transfer_id')->references('id')->on('transfers')->restrictOnDelete();
        });

        DB::transaction(function () {
            $permissionId = DB::table('permissions')->insertGetId([
                'name' => 'transfer_receive',
                'label' => 'Receive Stock Transfers',
                'description' => 'Receive or reject transfers at an assigned destination warehouse.',
            ]);

            $editorRoles = DB::table('permission_role')
                ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
                ->where('permissions.name', 'transfer_edit')
                ->whereNull('permissions.deleted_at')
                ->distinct()
                ->pluck('role_id');

            foreach ($editorRoles as $roleId) {
                DB::table('permission_role')->insert(['permission_id' => $permissionId, 'role_id' => $roleId]);
            }
        });
    }

    public function down(): void
    {
        // Dispatched stock and receipt history cannot be reconstructed by the old workflow.
        if (DB::table('transfers')->whereNotNull('dispatched_at')->exists()) {
            throw new RuntimeException('Cannot roll back branch receiving after a transfer has been dispatched.');
        }

        $permissionIds = DB::table('permissions')->where('name', 'transfer_receive')->pluck('id');
        DB::table('permission_role')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();

        Schema::dropIfExists('transfer_receipts');
        Schema::table('transfer_details', fn (Blueprint $table) => $table->dropColumn(['dispatched_stock_quantity', 'received_quantity']));
        Schema::table('transfers', fn (Blueprint $table) => $table->dropColumn(['receipt_status', 'receipt_version', 'dispatched_by', 'dispatched_at']));
    }
};
