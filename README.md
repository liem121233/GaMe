🔥 EMBER SPIRE

لعبة بطاقات وقتال تعمل محليًا على Windows باستخدام:

PHP

SQLite

HTML

CSS

JavaScript

مهم: اللعبة تعتمد على PHP وSQLite، لذلك لا تفتح index.html مباشرة بالدبل كليك. يجب تشغيلها من خلال PHP Local Server.

📋 محتويات README

فكرة اللعبة

متطلبات التشغيل

شكل مجلد المشروع

تثبيت وتجهيز PHP

التأكد من PHP

تشغيل اللعبة من Kiro

تشغيل اللعبة من CMD

فتح اللعبة في المتصفح

قاعدة البيانات SQLite

متطلبات PHP Extensions

الأخطاء الشائعة وحلولها

إيقاف السيرفر

تشغيل اللعبة مرة أخرى

ملفات المشروع ووظيفة كل ملف

ملاحظات مهمة

🔥 فكرة اللعبة

EMBER SPIRE هي لعبة بطاقات وقتال.

واجهة البداية تصف اللعبة بأنها:

ابنِ مجموعتك من الأوراق. اهبط عبر ستة طوابق. واجه ملك الجوف في القمة.

اللعبة تحتوي على نظام تشغيل Run، بطاقات، أعداء، طوابق، طاقة، نقاط حياة، مكافآت، وقائمة متصدرين.

💻 متطلبات التشغيل

نظام التشغيل

اللعبة مصممة للعمل على Windows.

PHP

تحتاج إلى PHP مثبت على الجهاز.

يمكن استخدام PHP 8.x، والنسخة المستخدمة أثناء إعداد المشروع هي:

PHP 8.5.11

PHP Extensions المطلوبة

يجب أن تكون الإضافات التالية متاحة:

pdo_sqlite
sqlite3
mbstring

المتصفح

يمكن استخدام أي متصفح حديث مثل:

Firefox

Chrome

Edge

لا تحتاج إلى

لا تحتاج اللعبة إلى:

Apache

XAMPP

MySQL

Internet

Unity

Node.js

لتشغيل النسخة المحلية الحالية يكفي PHP Local Server + SQLite.

📁 شكل مجلد المشروع

يجب أن يكون ترتيب الملفات بهذا الشكل تقريبًا:

Ember Spire/
│
├── index.html
├── common.php
├── config.php
├── schema.sql
├── game.db
│
├── api/
│   ├── start.php
│   ├── state.php
│   ├── play.php
│   ├── end_turn.php
│   ├── advance.php
│   └── leaderboard.php
│
├── css/
│   └── style.css
│
└── js/
    └── game.js

لماذا ترتيب المجلدات مهم؟

ملفات الـAPI تستخدم مسارات مثل:

require_once __DIR__ . '/../common.php';

وهذا يعني أن ملفات الـAPI يجب أن تكون داخل مجلد:

api/

وفي نفس الوقت، ملف:

index.html

يستدعي ملفات الواجهة من:

css/style.css
js/game.js

لذلك لا تغيّر ترتيب المجلدات عشوائيًا.

🐘 تثبيت وتجهيز PHP

إذا كان PHP مثبتًا بالفعل عندك في:

C:\php\php.exe

فأنت لا تحتاج إلى تثبيته مرة أخرى.

اختبار PHP

افتح CMD أو Terminal واكتب:

php -v

إذا ظهر شيء مثل:

PHP 8.5.11 (cli)

فإن PHP يعمل بشكل صحيح.

⚠️ إذا ظهر:

php is not recognized

فهذا يعني أن Windows لا يجد php.exe من خلال PATH.

إذا كان PHP موجودًا في:

C:\php\php.exe

يمكنك أولًا اختباره مباشرة:

C:\php\php.exe -v

إذا نجح الأمر، فالمشكلة في PATH فقط.

إضافة PHP إلى PATH

افتح CMD كمسؤول واكتب:

setx PATH "%PATH%;C:\php"

بعدها:

أغلق CMD.

أغلق Kiro بالكامل.

افتح Kiro من جديد.

افتح Terminal جديد.

اكتب:

php -v

إذا ظهر إصدار PHP، أصبح الأمر php متاحًا مباشرة.

إذا لم يعمل php بعد ذلك، يمكنك تشغيل اللعبة باستخدام المسار الكامل:

C:\php\php.exe -S localhost:8000

🔍 التأكد من PHP Extensions

اكتب:

php -m

وابحث عن:

PDO
pdo_sqlite
sqlite3
mbstring

يمكن أيضًا فحص كل واحدة بشكل منفصل:

php -m | findstr /I "PDO"

ثم:

php -m | findstr /I "sqlite"

ثم:

php -m | findstr /I "mbstring"

يجب أن تكون:

pdo_sqlite
sqlite3
mbstring

متاحة.

🧩 تشغيل اللعبة من Kiro

الخطوة 1 — افتح المشروع

في Kiro:

File → Open Folder

واختر مجلد:

Ember Spire

الخطوة 2 — افتح Terminal

من Kiro افتح:

Terminal

ثم تأكد أنك داخل مجلد اللعبة.

يمكنك استخدام:

dir

ويفترض أن ترى مثلًا:

index.html
common.php
config.php
schema.sql
game.db
api
css
js

▶️ الخطوة 3 — تشغيل PHP Server

إذا كان الأمر php يعمل:

php -S localhost:8000

إذا لم يكن يعمل، استخدم:

C:\php\php.exe -S localhost:8000

إذا كان كل شيء صحيحًا، سيظهر في Terminal شيء قريب من:

PHP Development Server
Listening on http://localhost:8000

🌐 الخطوة 4 — فتح اللعبة

افتح المتصفح واكتب:

http://localhost:8000

ثم اضغط Enter.

ستظهر شاشة:

EMBER SPIRE

وبعدها يمكنك الضغط على:

ابدأ الصعود

🚫 مهم جدًا: لا تفتح index.html مباشرة

لا تفعل:

Double Click → index.html

ولا تستخدم:

file:///...

لأن اللعبة تعتمد على ملفات PHP وطلبات API.

الصحيح هو:

php -S localhost:8000

ثم:

http://localhost:8000

🗄️ قاعدة البيانات SQLite

اللعبة تستخدم:

game.db

وقاعدة البيانات مبنية باستخدام SQLite.

ملف:

config.php

يقوم بفتح قاعدة البيانات:

game.db

ويستخدم ملف:

schema.sql

للتأكد من وجود الجداول المطلوبة.

🧱 إنشاء قاعدة البيانات

إذا لم يكن:

game.db

موجودًا، يستطيع إعداد اللعبة إنشاء قاعدة البيانات باستخدام:

schema.sql

لكن إذا كان game.db موجودًا بالفعل، لا تحذفه بدون سبب.

قاعدة البيانات تحتوي على بيانات مرتبطة باللعبة مثل:

Runs

Cards

Enemy State

Leaderboard

🧪 اختبار اللعبة

بعد فتح:

http://localhost:8000

جرّب التالي:

1. بدء لعبة

اضغط:

ابدأ الصعود

2. ظهور القتال

يجب أن تبدأ اللعبة بحالة قتال.

3. استخدام البطاقات

جرّب لعب بطاقة من المجموعة.

4. إنهاء الدور

استخدم زر إنهاء الدور إذا كان ظاهرًا.

5. التقدم

بعد إنهاء القتال، يجب أن تستطيع التقدم حسب حالة اللعبة.

🧩 ملفات المشروع ووظيفة كل ملف

index.html

واجهة اللعبة الرئيسية.

يحتوي على:

شاشة البداية

عنوان اللعبة

معلومات اللاعب

منطقة القتال

البطاقات

الأزرار

ويستدعي:

css/style.css
js/game.js

common.php

ملف مشترك تستخدمه ملفات PHP.

يحتوي على أنظمة اللعبة الأساسية مثل:

تعريف الطوابق

الأعداء

البطاقات

الـRun

إنشاء الأعداء

التعامل مع المجموعة

وظائف مشتركة للـAPI

config.php

مسؤول عن إعداد الاتصال بقاعدة البيانات وتهيئة البيئة.

يبدأ Session ويستخدم:

game.db

كما يعتمد على:

schema.sql

schema.sql

يحتوي على بنية قاعدة بيانات SQLite.

يحدد الجداول المستخدمة في اللعبة.

game.db

قاعدة البيانات الفعلية.

لا تعدّلها يدويًا إلا إذا كنت تعرف ما الذي تفعله.

api/start.php

يبدأ Run جديد.

يقوم بتهيئة أشياء مثل:

HP

Energy

Floor

Phase

Starter Deck

أول Enemy

أول Cards في اليد

api/state.php

يعيد الحالة الحالية للعبة.

api/play.php

مسؤول عن لعب بطاقة.

حسب البطاقة يمكن تنفيذ تأثيرات مثل:

Damage

Block

Heal

Vulnerable

Draw

ويمكن أن يؤدي هزيمة العدو إلى الحصول على مكافأة أو الوصول إلى Victory.

api/end_turn.php

مسؤول عن إنهاء دور اللاعب.

يقوم بمعالجة دور العدو، ثم التعامل مع السحب والتخلص من البطاقات وبدء الدور التالي.

api/advance.php

مسؤول عن التقدم في اللعبة بين الحالات المختلفة مثل:

Reward

Rest

Floor progression

كما يتعامل مع إنشاء العدو التالي وسحب البطاقات.

api/leaderboard.php

مسؤول عن الـLeaderboard.

يمكنه قراءة النتائج والتعامل مع اسم اللاعب المرتبط بأحدث نتيجة.

css/style.css

تصميم واجهة اللعبة.

يتحكم في:

الألوان

الأحجام

البطاقات

الأزرار

التخطيط

شاشة اللعبة

js/game.js

الجزء JavaScript المسؤول عن التفاعل مع الواجهة والتواصل مع الـAPI.

🛠️ تشغيل اللعبة باستخدام أمر واحد

إذا كنت داخل مجلد اللعبة وكان PHP موجودًا في PATH:

php -S localhost:8000

ثم:

http://localhost:8000

🛑 إيقاف اللعبة

لإيقاف PHP Server:

اضغط داخل Terminal:

Ctrl + C

بعدها سيتوقف السيرفر.

🔄 تشغيل اللعبة مرة أخرى

افتح Kiro.

افتح مجلد:

Ember Spire

افتح Terminal.

ثم:

php -S localhost:8000

ثم افتح:

http://localhost:8000

❌ أشهر الأخطاء وحلولها

الخطأ 1

php is not recognized

الحل

جرّب:

C:\php\php.exe -v

إذا نجح:

C:\php\php.exe -S localhost:8000

ثم أصلح PATH لاحقًا.

❌ الخطأ 2

could not find driver

هذا يعني غالبًا أن SQLite/PDO SQLite غير متاح.

تأكد من:

php -m

وابحث عن:

PDO
pdo_sqlite
sqlite3

❌ الخطأ 3

mbstring

إذا ظهر خطأ متعلق بـmbstring، تأكد من وجود:

mbstring

في:

php -m

❌ الخطأ 4

Failed opening required "../common.php"

غالبًا ترتيب المجلدات غير صحيح.

تأكد أن:

common.php

موجود في المجلد الرئيسي.

وأن ملفات الـAPI موجودة داخل:

api/

مثل:

api/play.php
api/start.php
api/state.php

❌ الخطأ 5

الصفحة تظهر بدون CSS

تأكد من وجود:

css/style.css

وأن index.html يستطيع الوصول إليه.

❌ الخطأ 6

اللعبة تفتح لكن JavaScript لا يعمل

تأكد من وجود:

js/game.js

ثم افتح Developer Tools في المتصفح باستخدام:

F12

وتحقق من Console.

❌ الخطأ 7

المنفذ 8000 مستخدم

إذا ظهر أن Port 8000 مستخدم، يمكنك تشغيل اللعبة على منفذ آخر:

php -S localhost:8080

ثم افتح:

http://localhost:8080

⚠️ لا تغلق Terminal أثناء اللعب

عندما تشغل:

php -S localhost:8000

يجب أن يظل Terminal مفتوحًا.

إذا أغلقت Terminal، سيتوقف PHP Server وستتوقف اللعبة عن الوصول إلى ملفات PHP.

🔐 ملاحظات عن localhost

العنوان:

localhost

يعني أن اللعبة تعمل على جهازك.

العنوان:

http://localhost:8000

ليس موقعًا عامًا على الإنترنت.

📌 تشغيل سريع

بعد تجهيز PHP، كل مرة تريد تشغيل اللعبة:

cd "مسار مجلد Ember Spire"

ثم:

php -S localhost:8000

ثم افتح:

http://localhost:8000

🚀 ملخص كامل من الصفر

1

تأكد أن PHP موجود:

C:\php\php.exe -v

2

تأكد من Extensions:

C:\php\php.exe -m

وابحث عن:

pdo_sqlite
sqlite3
mbstring

3

افتح مشروع Ember Spire في Kiro.

4

افتح Terminal.

5

انتقل إلى مجلد اللعبة.

6

شغل:

C:\php\php.exe -S localhost:8000

أو إذا كان PATH مضبوطًا:

php -S localhost:8000

7

افتح:

http://localhost:8000

8

اضغط:

ابدأ الصعود

9

لا تغلق Terminal أثناء اللعب.

10

عند الانتهاء اضغط:

Ctrl + C

لإيقاف السيرفر.

📦 ملاحظات التطوير

هذا المشروع عبارة عن لعبة ويب محلية، لذلك يمكن تطويره من Kiro مباشرة.

يمكن تعديل:

index.html

لتطوير الواجهة.

يمكن تعديل:

css/style.css

لتغيير التصميم.

يمكن تعديل:

js/game.js

لتطوير التفاعل.

يمكن تعديل ملفات:

api/

لتطوير منطق اللعبة والـAPI.

ويمكن تعديل:

common.php

لتطوير أنظمة اللعبة المشتركة مثل البطاقات والأعداء والطوابق.

🎮 EMBER SPIRE

ابنِ مجموعتك من الأوراق.
اهبط عبر ستة طوابق.
واجه ملك الجوف في القمة.

📝 ملاحظات

هذا README يشرح تشغيل النسخة الحالية من المشروع كما هي، ولا يفترض وجود خدمات خارجية أو API خارجي.

إذا ظهرت رسالة خطأ أثناء التشغيل، احتفظ برسالة الخطأ كاملة من Terminal لأنها تساعد في تحديد المشكلة بدقة.
