# نشر نسخة عرض براند العراقي على Hostinger

تُنفّذ هذه الخطوات مرة واحدة على قاعدة MySQL جديدة وفارغة. ملف SQL المرفق يحتوي بيانات تشغيل سابقة؛ يجب تمريره عبر الفلتر أدناه لاستيراد هيكل الجداول والبيانات المرجعية فقط.

1. من hPanel أنشئ قاعدة MySQL ومستخدمًا، ثم اضبط بيانات الاتصال في `.env` داخل `public_html`. استخدم `APP_ENV=production` و`APP_DEBUG=false` و`APP_URL=https://teal-toad-150300.hostingersite.com`، وأدخل `DB_HOST` و`DB_DATABASE` و`DB_USERNAME` و`DB_PASSWORD` التي يعرضها hPanel. لا تضع ملف `.env` في Git.
2. من مجلد المشروع أنشئ مفتاح Laravel ومجلدات الكتابة:

   ```bash
   cd ~/domains/teal-toad-150300.hostingersite.com/public_html
   php artisan key:generate --force
   mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
   chmod -R 775 storage bootstrap/cache
   ```

3. حمّل ملف المشروع `stocky database file.sql` إلى حساب Hostinger إن لم يكن مضمّنًا في نسخة GitHub. من جذر المشروع نفّذ هذا الأمر، واستبدل اسم القاعدة بمعلومات hPanel. سيطلب MySQL كلمة المرور تفاعليًا. لا تستخدم الأمر على قاعدة فيها بيانات:

   ```bash
   awk -f database/iraqi-demo-schema.awk 'stocky database file.sql' | mysql --default-character-set=utf8mb4 -h MYSQL_HOST -u MYSQL_USERNAME -p MYSQL_DATABASE
   ```

   استبدلي `MYSQL_HOST` و`MYSQL_USERNAME` و`MYSQL_DATABASE` بقيم hPanel. سيطلب `-p` كلمة المرور تفاعليًا.
4. شغّل المهيئ مرة واحدة:

   ```bash
   php artisan iraqi:install-demo
   ```

   ينفّذ الأمر الترحيلات، يضيف بيانات العرض والأدوار، ويكتب علامة `storage/app/public/installed` التي توقف التحويل إلى `/setup`. يفحص أن قاعدة البيانات خالية من بيانات المستخدمين والمنتجات والمخازن قبل التهيئة. احفظ كلمات المرور التي يطبعها؛ لا تُخزّن في ملفات المشروع.
5. اربط Document Root للدومين بالمجلد `public_html/public` من hPanel. ثم نفّذ:

   ```bash
   php artisan storage:link
   php artisan optimize:clear
   php artisan config:cache
   ```

تسجيل الدخول يتم من `/login`، والمتجر من `/online_store`. حساب المدير يملك كل صلاحيات النظام. الكاشير مخصص لنقطة البيع ومربوط بفرع الزرقا؛ المدير المالي له صلاحيات التقارير والحسابات والمخازن كلها. كل كلمة مرور عشوائية تُطبع عند التهيئة الأولى فقط.
