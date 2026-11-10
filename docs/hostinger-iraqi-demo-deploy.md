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

   ينفّذ الأمر الترحيلات، ويجهّز مفاتيح Passport إذا كانت غير موجودة، ويضيف بيانات العرض والأدوار، ويكتب علامة `storage/app/public/installed` التي توقف التحويل إلى `/setup`. يفحص أن قاعدة البيانات خالية من بيانات المستخدمين والمنتجات والمخازن قبل التهيئة. المفاتيح الموجودة لا تُستبدل. احفظ كلمات المرور التي يطبعها؛ لا تُخزّن في ملفات المشروع.
5. اربط Document Root للدومين بالمجلد `public_html/public` من hPanel. ثم نفّذ:

   ```bash
   php artisan storage:link
   php artisan optimize:clear
   php artisan config:cache
   ```

تسجيل الدخول يتم من `/login`، والمتجر من `/online_store`. حساب المدير يملك كل صلاحيات النظام. الكاشير مخصص لنقطة البيع ومربوط بفرع الزرقا؛ المدير المالي له صلاحيات التقارير والحسابات والمخازن كلها. كل كلمة مرور عشوائية تُطبع عند التهيئة الأولى فقط.

## توقف الإدارة بعد تسجيل الدخول: Invalid key supplied

تم التحقق على الاستضافة أن تسجيل دخول المدير ينجح، لكن طلب `/api/get_user_auth` يرجع HTTP 500 من `League\OAuth2\Server\CryptKey` برسالة `Invalid key supplied`. لوحة الإدارة تعتمد على هذا الطلب قبل العرض. مفاتيح Passport خاصة بكل استضافة وغير مرفوعة إلى Git؛ لا تنسخ مفاتيح جهاز التطوير.

لنسخة مثبتة بالفعل، نفّذ عبر Terminal أو SSH على Hostinger:

```bash
cd ~/domains/teal-toad-150300.hostingersite.com/public_html
php artisan config:clear
php artisan passport:keys
php artisan optimize:clear
```

هذه الأوامر لا تغيّر كلمات المرور أو بيانات التشغيل. بعد ظهور نجاح إنشاء المفاتيح، أعد تحميل صفحة الدخول وسجّل الدخول من جديد.

إذا ظهر `Encryption keys already exist`، لا تضف `--force` تلقائيًا: راجع قراءة الملفين `storage/oauth-private.key` و`storage/oauth-public.key` بواسطة مستخدم PHP، وأي قيم `PASSPORT_PRIVATE_KEY` و`PASSPORT_PUBLIC_KEY` في بيئة الاستضافة. القيم غير الفارغة تتجاوز الملفات ويجب أن تحتوي مفتاح PEM صحيحًا. لا تشارك محتوى المفاتيح. يمكن الاطلاع على أسماء الملفات وصلاحياتها فقط باستخدام `ls -l storage/oauth-*.key`.

لا تعِد تشغيل مهيئ العرض أو Seeder التثبيت لإصلاح المشكلة؛ نسخة السيرفر فيها البيانات بالفعل.
