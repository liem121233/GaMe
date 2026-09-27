تشغيل Ember Spire على Windows
=============================

1) ثبّت PHP for Windows.
2) تأكد أن الأمر php يعمل من CMD بكتابة:
   php -v
3) إذا ظهر رقم إصدار PHP، اضغط مرتين على:
   تشغيل اللعبة.bat
4) سيفتح المتصفح تلقائيًا على:
   http://localhost:8000

مهم:
- لا تفتح index.html بالنقر عليه مباشرة.
- اترك نافذة PHP السوداء مفتوحة أثناء اللعب.
- اللعبة تنشئ game.db تلقائيًا.

إذا ظهر خطأ عن SQLite أو mbstring، فعّل هذه الإضافات في php.ini:
   extension=pdo_sqlite
   extension=sqlite3
   extension=mbstring
