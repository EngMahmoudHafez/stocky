# تطبيق هوية العراقي | ELERAKY

الشعار الكامل الأصلي محفوظ في `public/images/eleraky-logo.jpg` دون تغيير. الأيقونة الصغيرة مستوحاة من الرمز الموجود في الشعار، ومستخدمة في الإدارة والمتجر وأيقونات تثبيت التطبيق. ألوان الهوية أخضر `#214d35` وذهبي `#ae8d53` وكريمي `#fcf2d4`.

تم تفريغ خلفية شعار الموقع والأيقونة، ورجوع صورة بنر الرئيسية إلى `images/store/iraqi-demo-hero.svg`. ملفات النسخة المفرغة:

- `public/images/eleraky-icon-transparent.png`: لوجو الإدارة والهيدر والفوتر بخلفية شفافة.
- `public/images/eleraky-favicon-transparent.png`: أيقونة المتصفح بخلفية شفافة.
- `public/images/eleraky-logo-transparent.png`: الشعار الكامل المفرغ للاستخدام والتسليم.

أسماء الملفات الجديدة تتجاوز كاش الصور القديمة. أيقونات تثبيت التطبيق تحت `pwa_images` تحتفظ بخلفيتها الكريمية المناسبة للاقتصاص الدائري.

بعد رفع الملفات المعدّلة، نفّذ من جذر المشروع على Hostinger:

```bash
php artisan iraqi:apply-brand
php artisan view:clear
```

الأمر يحدّث إعدادات الهوية والنصوص وأسماء البراند والشركة فقط داخل نسخة العراقي الموجودة. يمكن إعادة تشغيله. يحتفظ بالحسابات وكلمات المرور وصلاحياتها والمنتجات والأسعار والمخزون والطلبات والمبيعات. لا تشغّل `iraqi:install-demo` أو Seeder التثبيت على قاعدة مستخدمة.

ملفات الصور الجديدة تحت `public/images/eleraky-*`، وأيقونات PWA تحت `public/pwa_images/`، مع نسخ أيقونات متوافقة في جذر المشروع. تحديث Service Worker وروابط الأيقونات يسمح بجلب الهوية الجديدة بدل الكاش القديم.

رسالة العميل في `docs/eleraky-client-message.md`. املأ خانات كلمات المرور قبل نسخها. عناوين الحسابات الثلاثة مطابقة لمهيئ Hostinger `IraqiDemoSeeder`؛ راجعها من شاشة المستخدمين على الاستضافة لو اتغيّرت بعد التثبيت. كلمات المرور القديمة لا يمكن استخراجها من الـhash، ولم يتم تغيير أي كلمة مرور هنا. حسابات الإدارة منفصلة عن حسابات عملاء المتجر.

المنتجات والأسعار والأرصدة الحالية بيانات عرض. لا توجد أرقام اتصال أو حسابات تواصل اجتماعي مؤكدة في اللوجو؛ تُضاف عند استلامها من العميل.

## مصدر الأيقونة

تم استخدام أداة imagegen المدمجة لاشتقاق أيقونة من الشعار، ثم تصدير مقاسات 64 و192 و512 وملف ICO. الطلب المستخدم:

> Use case: precise-object-edit. Asset type: square website favicon and app icon for the supplied existing ELERAKY / العراقي brand. Edit target: supplied logo. Extract ONLY the existing central dark forest-green and antique-gold calligraphic monogram inside the circular emblem, including its circular green/gold outline. Omit the portrait/man/hat entirely, omit ALL Arabic and Latin text below the circle, omit hashtag and tagline. Preserve the shape of the existing calligraphic mark faithfully, do not invent a replacement letter. Center the resulting emblem large on the exact warm ivory cream background of the original. Clean flat reproduction with no shadows, no mockup, no extra decorations. Square canvas; emblem fits safely inside centered 76 percent width and height for maskable icons. Output a single crisp icon.

## تفريغ الخلفية

تم استخدام imagegen المدمجة مع `transparent_background=true`. طلب تفريغ أيقونة الموقع:

> Use case: background-extraction. Edit target: supplied ELERAKY website emblem icon. Remove ONLY the ivory/cream background, including cream negative-space holes inside and between the calligraphic strokes and circular outline. Return a genuinely transparent RGBA PNG. Preserve the existing dark forest-green and antique-gold artwork exactly: same monogram, circle, colors, stroke shapes, proportions, centered framing and margins. Do not redraw, stylize, add or remove strokes. No cream rectangle, no white fill, no checkerboard baked into the image, no shadow, no text. This is a clean background removal, not a redesign.

طلب تفريغ الشعار الكامل:

> Use case: background-extraction. Edit target: supplied original العراقي / ELERAKY brand logo. Remove ONLY the pale cream background, including all background gaps around and between the artwork and lettering. Return a genuinely transparent RGBA PNG. Preserve ALL original artwork exactly, including the man's portrait, hat, ivory shirt as foreground, circular dark forest-green and antique-gold monogram, and ALL original Arabic calligraphy and Latin lettering. Text must remain exactly as supplied: العراقي, بتاع جمله, hashtag, ELERAKY. Keep original arrangement, colors, proportions, framing and facial identity. Do not redesign or add anything. No white or cream rectangular canvas, no checkerboard baked into the image, no shadows. Preserve original ivory foreground areas such as the shirt; only remove background.

## التحقق المحلي

- تم تطبيق الأمر مرتين داخل transaction على قاعدة العرض الفعلية، والتحقق من بقاء بصمات جداول المستخدمين والأدوار وصلاحيات الفروع والمنتجات والمخزون والمبيعات والمشتريات والعملاء والمخازن والطلبات دون تغيير. تم التحقق أيضًا من رفض اسم شركة مختلف دون تغيير المتجر، ثم تطبيق الهوية محليًا.
- تجميع قوالب Blade وفحص PHP وJavaScript وJSON نجح، وفحص Pint للملفين الجديدين نجح.
- اختبارات Unit: ستة اختبارات وعشرة assertions ناجحة، مع تنبيه PHPUnit قديم في إعدادات المشروع. PHPStan غير مثبت في المشروع، فلم يتم تشغيله.
- تمت معاينة المتجر وصفحة الدخول على الكمبيوتر وعرض 375 بكسل، دون تمرير أفقي. لم يتم اختبار تسجيل الدخول إلى حسابات Hostinger لأن كلمات مرورها لم تُقدّم، ولم تُنشر التغييرات على السيرفر.
