# دليل العميل لتشغيل TechMizane POS

## 1) ما هو TechMizane؟
TechMizane هو نظام Point of Sale متكامل لإدارة البيع، الطاولات، المطبخ، الدفع، المخزون، والمشتريات من الموردين داخل نفس المنصة.  
الهدف منه هو توحيد العمل اليومي من لحظة أخذ الطلب إلى غاية التحصيل والمتابعة المالية.

هذا الدليل مكتوب للعميل النهائي (صاحب النشاط أو فريق التشغيل)، مع الحفاظ على أسماء الشاشات والأزرار الأساسية بالفرنسية كما تظهر داخل النظام.

---

## 2) الأدوار داخل النظام وكيف يستعمل كل دور المنصة

### Admin
يستعمل كل الأقسام:
- Principal: Dashboard, POS, Ventes, Tables
- Opérations: Serveur, Cuisine, Caisse
- Inventaire: Produits, Catégories, Stock
- Fournisseurs: Commandes, Fournisseurs
- Finance: Paiements, Historique Caisse
- Paramètres: Système, Utilisateurs, Permissions

### Serveur
يستعمل غالبا:
- Tables
- Serveur
- Mes Commandes

### Caissier
يستعمل غالبا:
- POS
- Caisse
- Ventes
- Paiements
- Historique Caisse

ملاحظة تشغيلية: Admin عنده صلاحية عامة ويمكنه الدخول إلى كل المسارات.

---

## 3) خريطة الوظائف الأساسية في POS

### A) البيع المباشر عبر شاشة POS
هذا السيناريو مناسب للطلبات السريعة بدون طاولة.

طريقة العمل:
1. دخول إلى صفحة POS.
2. البحث عن المنتج أو التصفية عبر Catégories.
3. إضافة المنتجات إلى Panier.
4. اختيار Mode de paiement: Espèces أو Carte أو Mixte.
5. الضغط على Valider la vente.

النتيجة:
- إنشاء Vente مباشرة.
- تسجيل Paiement مباشرة.
- خصم الكمية من Stock تلقائيا.

هذا السيناريو مناسب جدا لـ snack، vente au comptoir، takeaway.

### B) تشغيل الطاولات عبر Tables + POS
هذا السيناريو مناسب للمطاعم والمقاهي التي تعتمد خدمة الطاولة.

طريقة العمل:
1. دخول إلى صفحة Tables.
2. فتح طاولة عبر Ouvrir une commande أو من POS مع table.
3. إضافة العناصر عبر Ajouter des articles.
4. الطلب يبقى unpaid إلى حين Encaisser.
5. عند التحصيل من table detail أو cashout يتم تحرير الطاولة.

عمليات مهمة في Tables:
- Marquer comme occupée
- Libérer la table
- Transférer
- Encaisser
- Voir les détails

### C) مسار Serveur إلى Cuisine إلى Caisse (Commande cuisine)
هذا هو المسار الكامل لخدمة القاعة.

1. Serveur يفتح Table من شاشة Serveur.
2. يضيف المنتجات، الملاحظات الخاصة لكل منتج، و Notes générales.
3. يضغط Envoyer à la cuisine.
4. الطلب يظهر في Cuisine Dashboard.
5. فريق المطبخ يغير الحالة حسب التحضير.
6. عندما يصبح الطلب جاهزا ينتقل إلى Caisse للتحصيل.

هذا المسار هو الأفضل للأنشطة التي تريد فصل واضح بين أخذ الطلب، التحضير، والتحصيل.

### D) Commandes fournisseurs (الشراء من المورد)
مخصص لإعادة التموين.

طريقة العمل:
1. فتح صفحة Commandes fournisseurs.
2. إنشاء Nouvelle commande.
3. اختيار Fournisseur وإضافة المنتجات والكميات وسعر الشراء.
4. عند وصول البضاعة يتم Marquer comme reçue.

النتيجة:
- تحديث Stock بالزيادة بعد الاستلام.
- تتبع واضح للطلبات pending و received.

### E) إدارة المخزون
من صفحات Produits و Stock:
- إضافة أو تعديل منتج.
- ضبط alert_stock.
- تنفيذ Nouveau mouvement.
- تتبع Entrée و Sortie و raison: vente, commande, perte, ajustement.

### F) المتابعة المالية
من صفحات Ventes و Paiements و Historique Caisse:
- مراقبة كل المبيعات.
- متابعة طرق الدفع (Espèces, Carte, Mixte).
- إصدار reçu/receipt.
- قراءة المؤشرات اليومية.

### G) الإدارة والتأمين
من Paramètres:
- Système: إعدادات المؤسسة.
- Utilisateurs: إدارة الحسابات والأدوار.
- Permissions: التحكم في صلاحيات كل مستخدم.

---

## 4) شرح الشاشة التي تلي الطلب: من التحضير حتى يصبح جاهزا

هذا الجزء مهم كما طلبت، لأنه يصف رحلة الطلب بعد إرساله من Serveur.

## المرحلة 1: إرسال الطلب
- الشاشة: Serveur ثم Commande - Table X
- الزر الرئيسي: Envoyer à la cuisine
- الحالة الأولية للطلب: en_cuisine

## المرحلة 2: استلام الطلب داخل Cuisine
- الشاشة: Cuisine Dashboard
- يظهر الطلب تحت قسم En cuisine.
- الإجراء: الضغط على Valider.
- بعد الضغط، الحالة تصبح en_preparation.

## المرحلة 3: أثناء التحضير
- الطلب يتحول إلى عمود En préparation.
- الفريق يشتغل على العناصر كما هي مع notes الخاصة.
- عند الانتهاء: الضغط على Commande prête.

## المرحلة 4: الطلب جاهز
- الحالة تصبح pret.
- يظهر وضع PRÊT POUR LA CAISSE.
- في Cuisine Display يظهر في عمود Prêt à servir.
- يمكن تشغيل التنبيه الصوتي لتكرار الإعلان عن الطلب الجاهز.

## المرحلة 5: التحصيل في Caisse
- الشاشة: Commandes en attente de paiement.
- الطلب الجاهز يظهر مع زر Encaisser.
- الانتقال إلى شاشة Paiement واختيار Espèces أو Carte أو Mixte.
- بعد Valider le paiement تنتقل الحالة إلى payee.
- الطاولة تتحرر تلقائيا إذا لا توجد أوامر أخرى معلقة لنفس الطاولة.

---

## 5) حالات الاستخدام حسب نوع النشاط

## 1. Café (مقهى)
الأنسب:
- Tables
- Serveur
- Cuisine
- Caisse
- Paiements

طريقة مقترحة:
- الطلبات من الطاولات عبر Serveur.
- المشروبات/الأكلات الخفيفة تتحرك عبر Cuisine Dashboard.
- التحصيل النهائي من Caisse أو Encaisser داخل Tables.

## 2. Restaurant service complet
الأنسب:
- نفس مسار café لكن باعتماد أقوى على notes و waiter_notes.
- استعمال Transférer بين الطاولات عند تغيير مكان الزبون.
- متابعة الأداء عبر Dashboard و Historique Caisse.

## 3. Snack / Fast-food
الأنسب:
- POS Direct كأساس.
- Caisse للطلبات الجاهزة من المطبخ إذا كان عندك prep line.
- تقليل الاعتماد على Tables إذا النشاط comptoir فقط.

## 4. Salon de thé / Pâtisserie
الأنسب:
- مزيج بين POS Direct و Tables.
- تتبع دقيق لـ Produits و alert_stock لأن الأصناف كثيرة وسريعة الدوران.

## 5. Bar avec terrasse
الأنسب:
- Tables مع zones (Intérieur, Terrasse, Bar).
- Serveur لتوزيع العمل.
- متابعة مدة إشغال الطاولة وقيمة كل table مباشرة.

## 6. Restaurant + Takeaway
الأنسب:
- داخل القاعة: Serveur -> Cuisine -> Caisse.
- خارج القاعة: POS Direct.
- نفس قاعدة البيانات تعطيك رؤية موحدة للمبيعات.

## 7. Food court أو نشاط متعدد الكاشير
الأنسب:
- اعتماد قوي على Caisse و Historique Caisse.
- Utilisateurs و Permissions لتنظيم الوصول حسب كل مستخدم.

## 8. Catering / événements صغيرة
الأنسب:
- استعمال POS Direct للمبيعات السريعة.
- استخدام Commandes fournisseurs للتحضير المسبق للمواد.
- مراقبة الكلفة والبيع عبر Ventes + Paiements.

---

## 6) سيناريوهات تشغيل يومية عملية

## بداية اليوم
1. Admin أو المسؤول يراجع Dashboard.
2. التحقق من low stock في Produits/Stock.
3. فتح الشاشات حسب الفريق: Serveur, Cuisine, Caisse.

## أثناء الخدمة
1. Serveur يدخل الطلبات من الطاولات.
2. Cuisine يتابع الحالات: en_cuisine ثم en_preparation ثم pret.
3. Caisse يجمع المدفوعات عند الجاهزية.
4. متابعة الحالات غير المغلقة في Tables.

## نهاية اليوم
1. مراجعة Historique Caisse.
2. فحص Paiements حسب méthode.
3. مراجعة Ventes وحالات annulée إن وجدت.
4. تحضير Commandes fournisseurs إذا stock منخفض.

---

## 7) أهم الحالات الخاصة وكيف تتصرف

### حالة: الطاولة لا يمكن تحريرها
السبب غالبا: الطلب غير مدفوع.
الحل: نفذ Encaisser أولا ثم Libérer la table.

### حالة: زبون غير مكانه
الحل: استخدم Transférer من Tables أو table detail.

### حالة: دفع مختلط
الحل: في Paiement اختر Mixte وأدخل cash_amount + card_amount.

### حالة: طلب يحتاج ملاحظة خاصة
الحل: أضف notes لكل منتج، و Notes générales للطلب ككل قبل Envoyer à la cuisine.

### حالة: نفاد منتج
الحل: راجع Produits أو نفذ Nouveau mouvement في Stock، ثم يمكن الإرجاع إلى البيع.

---

## 8) مؤشرات الأداء التي يهم العميل متابعتها

- Ventes اليوم (القيمة وعدد العمليات).
- توزيع Paiements حسب Espèces/Carte/Mixte.
- عدد الطلبات en_preparation و pret في Cuisine.
- Taux d'occupation للطاولات.
- منتجات low stock.

هذه المؤشرات تساعد صاحب النشاط على تحسين:
- سرعة الخدمة
- تنظيم الفريق
- التحكم في المخزون
- دقة التحصيل

---

## 9) توصية تطبيق حسب طبيعة العميل

إذا نشاطك:
- يعتمد الطاولات: اجعل المسار الافتراضي Serveur -> Cuisine -> Caisse.
- يعتمد البيع السريع: اجعل POS Direct هو المسار الأساسي.
- مختلط: شغّل المسارين معا داخل نفس النظام.

أفضل ممارسة:
- تدريب الفريق على شاشة واحدة لكل دور.
- توحيد أسماء العمليات داخل المؤسسة بنفس أسماء النظام (مثلا: Encaisser, Commande prête, Table occupée).
- مراجعة يومية قصيرة من Dashboard و Historique Caisse.

---

## 10) خلاصة
TechMizane قابل للاستعمال في café، restaurant، snack، bar، salon de thé، takeaway، ونماذج تشغيل أخرى متعددة.  
قوة النظام في أنه يجمع:
- POS Direct
- Tables
- Commandes cuisine
- Caisse
- Stock
- Commandes fournisseurs
- Paiements
- Paramètres

داخل دورة تشغيل واحدة واضحة، مع تتبع كامل من أول الطلب إلى التحصيل النهائي.
