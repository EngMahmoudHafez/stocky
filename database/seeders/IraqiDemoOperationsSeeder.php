<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\OnlineOrder;
use App\Models\ShippingMethod;
use App\Support\IraqiBrand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class IraqiDemoOperationsSeeder extends Seeder
{
    public function run(int $ownerId, array $warehouseIds, array $clientIds, array $stock): void
    {
        $cashId = DB::table('payment_methods')->insertGetId(['name' => 'نقدي']);
        DB::table('payment_methods')->insert(['name' => 'بطاقة بنكية']);
        DB::table('settings')->update(['default_payment_method_id' => $cashId]);

        $providerId = DB::table('providers')->insertGetId([
            'code' => 1, 'name' => 'مورد الدلتا للملابس • عرض', 'city' => 'المحلة الكبرى', 'country' => 'مصر',
        ]);

        foreach ($warehouseIds as $index => $warehouseId) {
            $this->receiveCatalog($stock, ['user_id' => $ownerId, 'warehouse_id' => $warehouseId, 'provider_id' => $providerId], $index);
        }

        $branches = array_slice($warehouseIds, 1);
        foreach (range(0, 17) as $index) {
            $this->recordSale($stock[$index * 7 % count($stock)], [
                'user_id' => $ownerId, 'warehouse_id' => $branches[$index % count($branches)],
                'client_id' => $clientIds[$index % count($clientIds)],
            ], $index, $cashId);
        }

        $this->createSampleOrder($stock[1], $warehouseIds[0], $clientIds[1]);
        $this->createStaff($ownerId);
    }

    private function createSampleOrder(array $variant, int $warehouseId, int $clientId): void
    {
        $client = Client::findOrFail($clientId);
        $shipping = ShippingMethod::firstOrFail();
        $order = OnlineOrder::create([
            'client_id' => $clientId, 'warehouse_id' => $warehouseId,
            'customer_name' => $client->name, 'customer_email' => $client->email,
            'shipping_address' => 'عنوان للعرض', 'shipping_city' => $client->city, 'shipping_country' => $client->country,
            'shipping_method_id' => $shipping->id, 'shipping_method_name' => $shipping->name,
            'subtotal' => $variant['price'], 'shipping_cost' => $shipping->price,
            'total' => $variant['price'] + $shipping->price,
            'status' => 'pending', 'payment_method' => 'cod', 'payment_status' => 'pending',
        ]);
        $order->items()->create(Arr::only($variant, ['product_id', 'product_variant_id', 'price']) + ['qty' => 1]);
    }

    private function receiveCatalog(array $stock, array $purchase, int $index): void
    {
        $quantity = $index === 0 ? 12 : 6;
        $purchaseId = DB::table('purchases')->insertGetId($purchase + [
            'Ref' => 'PR_'.(1001 + $index), 'date' => now()->subDays(20)->toDateString(),
            'GrandTotal' => array_sum(array_column($stock, 'cost')) * $quantity,
            'statut' => 'received', 'payment_statut' => 'unpaid',
        ]);

        foreach ($stock as $variant) {
            $variantKey = Arr::only($variant, ['product_id', 'product_variant_id']);
            DB::table('purchase_details')->insert($variantKey + [
                'purchase_id' => $purchaseId, 'purchase_unit_id' => $variant['unit_id'],
                'quantity' => $quantity, 'cost' => $variant['cost'], 'total' => $quantity * $variant['cost'],
            ]);
            DB::table('product_warehouse')->insert($variantKey + ['warehouse_id' => $purchase['warehouse_id'], 'qte' => $quantity]);
        }
    }

    private function recordSale(array $variant, array $sale, int $index, int $cashId): void
    {
        $date = now()->subDays(intdiv($index, 2))->toDateString();
        $quantity = 1 + $index % 2;
        $total = $variant['price'] * $quantity;
        $saleId = DB::table('sales')->insertGetId($sale + [
            'date' => $date, 'time' => '14:30:00', 'Ref' => 'SL_'.(1001 + $index), 'is_pos' => 1,
            'statut' => 'completed', 'GrandTotal' => $total, 'paid_amount' => $total, 'payment_statut' => 'paid',
        ]);
        $variantKey = Arr::only($variant, ['product_id', 'product_variant_id']);
        DB::table('sale_details')->insert($variantKey + [
            'sale_id' => $saleId, 'sale_unit_id' => $variant['unit_id'], 'date' => $date,
            'quantity' => $quantity, 'price' => $variant['price'], 'total' => $total,
        ]);
        DB::table('product_warehouse')->where($variantKey + ['warehouse_id' => $sale['warehouse_id']])->decrement('qte', $quantity);
        DB::table('payment_sales')->insert([
            'sale_id' => $saleId, 'date' => $date, 'Ref' => 'INV/SL_'.(1001 + $index),
            'montant' => $total, 'payment_method_id' => $cashId, 'user_id' => $sale['user_id'],
        ]);
    }

    private function createStaff(int $ownerId): void
    {
        $companyId = DB::table('companies')->insertGetId(['name' => IraqiBrand::NAME, 'country' => 'مصر']);
        $shiftId = DB::table('office_shifts')->insertGetId([
            'company_id' => $companyId, 'name' => 'وردية الفروع',
            ...array_fill_keys(['monday_in', 'tuesday_in', 'wednesday_in', 'thursday_in', 'friday_in', 'saturday_in', 'sunday_in'], '11:00'),
            ...array_fill_keys(['monday_out', 'tuesday_out', 'wednesday_out', 'thursday_out', 'friday_out', 'saturday_out', 'sunday_out'], '19:00'),
        ]);
        $staff = [
            ['ندى', 'علي', 'female', 'الزرقا', 'مبيعات الفروع', 'كاشير'],
            ['يوسف', 'حسن', 'male', 'المنصورة', 'مبيعات الفروع', 'مسؤول فرع'],
            ['هنا', 'سمير', 'female', 'دمياط الجديدة', 'مبيعات الفروع', 'مشرفة مبيعات'],
            ['عمر', 'خالد', 'male', 'دمياط', 'المخزن الرئيسي', 'أمين مخزن'],
        ];
        $departments = [];
        foreach ($staff as $index => [$first, $last, $gender, $city, $department, $job]) {
            $departmentId = $departments[$department] ??= DB::table('departments')->insertGetId([
                'company_id' => $companyId, 'department' => $department,
            ]);
            $this->createEmployee([
                'firstname' => $first, 'lastname' => $last, 'username' => 'iraqi-staff-'.($index + 1),
                'gender' => $gender, 'city' => $city, 'company_id' => $companyId,
                'department_id' => $departmentId, 'office_shift_id' => $shiftId,
            ], $job, $ownerId);
        }
    }

    private function createEmployee(array $employee, string $job, int $ownerId): void
    {
        $designationId = DB::table('designations')->insertGetId(Arr::only($employee, ['company_id', 'department_id']) + [
            'designation' => $job,
        ]);
        $employeeId = DB::table('employees')->insertGetId($employee + [
            'designation_id' => $designationId, 'country' => 'مصر', 'joining_date' => now()->subMonths(3)->toDateString(),
        ]);
        DB::table('attendances')->insert([
            'employee_id' => $employeeId, 'user_id' => $ownerId, 'company_id' => $employee['company_id'],
            'date' => now()->subDay()->toDateString(), 'clock_in' => '11:00', 'clock_out' => '19:00',
            'clock_in_ip' => '127.0.0.1', 'clock_out_ip' => '127.0.0.1', 'clock_in_out' => 0,
            'total_work' => '08:00', 'status' => 'present',
        ]);
    }
}
