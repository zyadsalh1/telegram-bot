# -*- coding: utf-8 -*-
import re
import asyncio
from collections import defaultdict

from telegram import Update, InputMediaPhoto
from telegram.ext import (
    Application,
    CommandHandler,
    MessageHandler,
    ContextTypes,
    filters,
)

# ===== إعدادات حساسة (لم تتغير كما طلبت) =======
TOKEN = "7908857505:AAFDI6iwJ6fHYiSEs_IftEfh4_iluMM7IhU"
CHANNEL = "@apple_planet_eg"   # قناة النشر
ALLOWED_GROUP_USERNAME = "ApplePlanett"  # جروب التاجر
BOT_HANDLE = "@apple_planet1_bot"

# ===== ملاحظة مهمة: ضع هنا رقم حسابك الشخصي (المدير) بالصيغة الرقمية =======
ADMIN_ID = 1316689052  # <-- غيّره لرقمك الشخصي في تيليجرام قبل التشغيل

# ===== شروط الضمان + الشحن (النصوص الأصلية، سنعرضها منسقة كـ Q&A) =======
POLICY_LINES = [
    "📦 الشحن 200 جنيه لجميع محافظات مصر (يُدفع مقدماً لتأكيد الأوردر).",
    "⏱️ مدة الشحن: 24–48 ساعة.",
    "🏠 متاح الاستلام من طنطا (بجوار المعهد العالي للهندسة والتكنولوجيا).",
    "💳 التقسيط متاح عن طريق فيزا مشتريات أي بنك / أمان / ڤاليو.",
    "",
    "🔒 شروط الضمان:",
    "1- مدة الضمان: شهر من تاريخ الشراء.",
    "2- ضمان البطارية: تشغيل فيديو ساعة واحدة. (البطارية/الشاحن ضمان أسبوع فقط).",
    "3- الضمان باطل عند فتح الجهاز أو إزالة استيكر الضمان.",
    "4- الضمان يشمل الأعطال الكبرى فقط.",
    "5- في حال تعذّر الإصلاح: استبدال بجهاز مماثل (لا يوجد استرجاع نقدي).",
]

# نسخة مفصّلة من شروط الضمان بصيغة أسئلة وإجابات (كما أعطيتني — رتبناها)
WARRANTY_QA = [
    ("ما مدة الضمان؟", "مدة الضمان هي شهر واحد من تاريخ الشراء الأصلي."),
    ("هل البطارية مشمولة؟", "يوجد ضمان للبطارية: فحص بتشغيل فيديو لمدة ساعة. ملاحظة: الضمان على البطارية/الشاحن يمتد لأسبوع واحد فقط."),
    ("متى يصبح الضمان لاغيًا؟", "الضمان يبطل في حالة فتح الجهاز أو فك الماذربورد أو إزالة استيكر الضمان أو إذا كان العطل نتيجة سوء استخدام أو تعديلات غير مصرح بها."),
    ("ما الذي يغطيه الضمان؟", "الضمان يشمل الأعطال الكبرى التي لا تنتج عن سوء الاستخدام. في حالة العطل، يقتصر التعويض على الإصلاح أو الاستبدال بجهاز مماثل إذا تعذّر الإصلاح."),
    ("هل هناك استرجاع نقدي؟", "لا يوجد استرجاع نقدي. في حال تعذّر الإصلاح بعد محاولات، يحق استبدال الجهاز بآخر مماثل فقط."),
    ("هل هناك مصاريف شحن للمطالبة بالضمان؟", "قد يتحمّل العميل جزءًا من مصاريف الشحن بناءً على الحالة (التفاصيل تتحدد عند التواصل مع خدمة العملاء)."),
    ("هل الكابلات والملحقات مضمونة؟", "الضمان لا يشمل الكابلات والملحقات البسيطة. ضمان الشاحن والبطارية محدود كما ذُكر أعلاه."),
]

# ===== قواعد حساب العمولة الجديدة (معدّلة حسب تعليماتك) =======
def commission_for(price: int) -> int:
    """
    قواعد العمولة الجديدة حسب ما طلبت:
    - العمولة بين 500 إلى 3000 جنيه فقط
    - العمولة تحدد بنسبة 5% من السعر مع تحديد حد أدنى وحد أقصى
    - ثم تقريب العمولة لأقرب 100 جنيه
    """
    # حساب العمولة كنسبة 5% من السعر
    commission_percentage = price * 0.05

    # تحديد الحد الأدنى (500) والحد الأقصى (3000)
    commission = max(500, min(commission_percentage, 3000))

    # تقريب العمولة لأقرب 100 جنيه
    commission = round(commission / 100) * 100

    return int(commission)

def round_to_nearest_100(price: int) -> int:
    """تقريب السعر لأقرب 100 جنيه (لجعله مئوي)"""
    return round(price / 100) * 100

def extract_price(text: str) -> int | None:
    """
    يستخرج السعر الصحيح من النص:
    - يبحث عن أنماط محددة مثل "السعر XXXXX" أو "سعر XXXXX"
    - يتجاهل الأرقام الصغيرة التي لا تمثل أسعار أجهزة واقعية
    """
    if not text:
        return None

    text_lower = text.lower()

    # أنماط للبحث عن السعر
    patterns = [
        r"السعر\s*[:\-]?\s*(\d{4,6})",
        r"سعر\s*[:\-]?\s*(\d{4,6})",
        r"ب\s*(\d{4,6})\s*ج",
        r"ب\s*(\d{4,6})\s*جنيه",
        r"(\d{4,6})\s*ج\.م",
        r"(\d{4,6})\s*جنيه",
    ]

    prices = []
    for pattern in patterns:
        matches = re.findall(pattern, text_lower)
        for match in matches:
            price = int(match.replace(",", "").replace(" ", ""))
            if 5000 <= price <= 200000:  # نطاق أسعار واقعي للأجهزة
                prices.append(price)

    # إذا وجدنا أسعاراً من الأنماط المحددة
    if prices:
        # إذا كان هناك إشارة إلى خصم (بدلاً من)، نأخذ أصغر سعر
        discount_keywords = ["بدلا", "بدلاً", "بدل", "instead of", "خصم", "من"]
        if any(k in text_lower for k in discount_keywords):
            return min(prices)
        else:
            return max(prices)

    # إذا لم نجد بأنماط محددة، نبحث عن أي أرقام في النص
    t_clean = text.replace(",", "").replace(" ", "")
    all_numbers = re.findall(r"\d{4,6}", t_clean)
    if not all_numbers:
        return None

    nums_int = [int(n) for n in all_numbers if 5000 <= int(n) <= 200000]
    if not nums_int:
        return None

    # إذا كان هناك إشارة إلى خصم، نأخذ أصغر سعر
    discount_keywords = ["بدلا", "بدلاً", "بدل", "instead of", "خصم", "من"]
    if any(k in text_lower for k in discount_keywords):
        return min(nums_int)

    # وإلا نأخذ أكبر رقم (على افتراض أنه السعر الأساسي)
    return max(nums_int)

def format_price(v: int) -> str:
    return f"{v:,}".replace(",", ",")

# ===== تخزين مؤقت للألبومات الواردة من الجروب =======
albums = defaultdict(lambda: {"photos": [], "caption": None})
admin_forward_map: dict[int, int] = {}  # mapping: forwarded_message_id (to admin) -> original_user_chat_id

# ===== وظائف مساعدة لتنظيف وتهيئة الوصف للإعلان =======
POLICY_KEYWORDS = [
    "شحن", "الضمان", "شروط", "التقسيط", "مدة الضمان", "البطارية",
    "الاستلام", "استلام", "التقسيط", "قيمة", "يُدفع", "جنيه", "EGP"
]

SPEC_KEYWORDS = [
    "RAM", "رام", "جيجا", "SSD", "HDD", "Intel", "i3", "i5", "i7", "i9",
    "Ryzen", "شاشة", "دقة", "سعة", "معالج", "هارد", "GHz", "هرتز", "جيجا"
]

def clean_caption_and_remove_price(caption: str, price_found: int) -> str:
    """
    يحذف أسطر السياسات والأسطر التي تحتوي على معلومات الشحن/الضمان،
    ويزيل أي ذكر للسعر من النص.
    يرجع النص النظيف كـ وصف تاجر.
    """
    lines = [l.strip() for l in caption.splitlines() if l.strip()]
    cleaned = []

    for l in lines:
        low = l.lower()

        # تجاهل أسطر السياسات
        if any(kw in low for kw in POLICY_KEYWORDS):
            continue

        # تجاهل الأسطر التي تحتوي على أرقام (أسعار محتملة)
        if re.search(r'\d{4,6}', l.replace(",", "").replace(" ", "")):
            # نتجاهل السطر الذي يحتوي على السعر
            continue

        cleaned.append(l)

    return "\n".join(cleaned).strip() if cleaned else ""

def extract_specs_and_desc(original_caption: str, cleaned_desc: str) -> (str, str):
    """
    يحاول استخراج أسطر المواصفات بناءً على كلمات مفتاحية للمواصفات،
    وما يتبقى يعتبر وصف البائع.
    """
    lines = [l.strip() for l in original_caption.splitlines() if l.strip()]
    spec_lines = [l for l in lines if any(kw in l for kw in SPEC_KEYWORDS)]

    # إزالة أسطر السياسات من الوصف
    desc_lines = [l for l in lines if l not in spec_lines and not any(kw in l for kw in POLICY_KEYWORDS)]

    # إذا لدينا cleaned_desc استخدمه كـ وصف البائع
    desc_text = cleaned_desc if cleaned_desc else ("\n".join(desc_lines) if desc_lines else "لا يوجد وصف تفصيلي من البائع.")
    specs_text = "\n".join(spec_lines) if spec_lines else "لم يتم إدراج مواصفات مفصّلة."

    return desc_text, specs_text

# ===== معالجة الألبومات (بعد تجميع صور الألبوم) =======
async def process_album(key: str, context: ContextTypes.DEFAULT_TYPE):
    entry = albums.pop(key, None)
    if not entry:
        return

    photos = entry["photos"]
    caption = entry["caption"] or ""
    price = extract_price(caption)

    if not price:
        # لو مفيش سعر واضح: نرسل تنبيه للادمن للمراجعة بدل النشر الآلي
        text = "⚠️ تم استلام إعلان بدون سعر واضح لنشره في القناة. الرجاء المراجعة يدويًا."
        # حاول نوجه رسالة للادمن مع تفاصيل الطلب
        try:
            await context.bot.send_message(chat_id=ADMIN_ID, text=f"{text}\n\nالنص الأصلي:\n{caption}")
        except Exception:
            pass
        return

    comm = commission_for(price)
    final_price = price + comm

    # تقريب السعر النهائي لأقرب 100 جنيه (جعله مئوي)
    final_price = round_to_nearest_100(final_price)

    # تنظيف الوصف وإعداد القالب النهائي
    cleaned_desc = clean_caption_and_remove_price(caption, price)

    # ===== التعديل المطلوب هنا فقط =====
    final_caption = (
        f"🔹 شركة Apple Planet تقدم:\n\n"
        f"{cleaned_desc}\n\n"
        f"💰 السعر: {format_price(final_price)} ج.م\n\n"
        f"للطلب أو الاستفسار: {BOT_HANDLE}"
    )

    # تجهيز الوسائط (نستخدم file_id الموجود)
    media = []
    for i, file_id in enumerate(photos):
        if i == 0:
            media.append(InputMediaPhoto(media=file_id, caption=final_caption))
        else:
            media.append(InputMediaPhoto(media=file_id))

    try:
        await context.bot.send_media_group(chat_id=CHANNEL, media=media)
    except Exception as e:
        # لو فشل النشر: أرسل تفاصيل للادمن
        await context.bot.send_message(chat_id=ADMIN_ID, text=f"خطأ عند نشر الألبوم: {e}\n\nالنص: {final_caption}")
        return

# مهمة مؤقتة لانتظار تجميع صور الالبوم
async def wait_and_process(key: str, context: ContextTypes.DEFAULT_TYPE):
    await asyncio.sleep(1.4)
    await process_album(key, context)

# ===== معالج وصول الصور من الجروب =======
async def handle_group_photos(update: Update, context: ContextTypes.DEFAULT_TYPE):
    msg = update.effective_message
    if msg.chat.username != ALLOWED_GROUP_USERNAME:
        return
    if not msg.photo:
        return

    group_id = msg.media_group_id or msg.message_id
    key = f"{msg.chat.id}_{group_id}"

    if msg.caption and msg.caption.strip():
        albums[key]["caption"] = msg.caption

    # نأخذ أكبر نسخة من الصورة (آخر عن�
    file_id = msg.photo[-1].file_id
    albums[key]["photos"].append(file_id)

    # إذا ما في media_group (صورة مفردة) عالج فوراً، وإلا انتظر تجميع الألبوم
    if not msg.media_group_id:
        await process_album(key, context)
    else:
        # جدولة الانتظار لمعرفة انتهاء وصول باقي الصور
        # تجنّب إنشاء أكثر من مهمة بنفس المفتاح
        asyncio.create_task(wait_and_process(key, context))

# ===== رد البداية (قائمة مرقّمة) =======
async def start(update: Update, context: ContextTypes.DEFAULT_TYPE):
    if update.effective_chat.type == "private":
        text = (
            "أهلاً بك في Apple Planet 👋\n\n"
            "اختر أحد الخيارات أو اكتب رقم السؤال:\n\n"
            "1️⃣  الشحن\n"
            "2️⃣  الضمان\n"
            "3️⃣  الدفع\n"
            "4️⃣  التقسيط\n"
            "5️⃣  شرح طريقة الشغل وتنزيل الإعلانات\n"
            "6️⃣  تواصل مع فريق المبيعات\n\n"
            "ملاحظة: يمكنك كتابة الرقم (1 أو ١) أو إرسال الإيموجي (1️⃣) أو كتابة الكلمة المفتاحية (مثلاً: الشحن) وسيتم الرد تلقائياً."
        )
        await update.message.reply_text(text)

# ===== نصوص الأسئلة الشائعة (مقسّمة) =======
def get_shipping_text() -> str:
    return (
        "📦 معلومات الشحن:\n"
        "- مصاريف الشحن: 200 جنيه لجميع محافظات مصر (يُدفع مقدماً لتأكيد الأوردر).\n"
        "- مدة الشحن المتوقعة: من 24 إلى 48 ساعة.\n"
        "- الاستلام المحلي متاح من طنطا (بجوار المعهد العالي للهندسة والتكنولوجيا).\n"
        "- لمزيد من التفاصيل أو ترتيب الاستلام، تواصل معنا عبر البوت."
    )

def get_warranty_text() -> str:
    lines = ["🔒 معلومات الضمان:"]
    for q, a in WARRANTY_QA:
        lines.append(f"\n🔹 {q}\n{a}")
    lines.append("\nللاستفسار أو فتح تذكرة ضمان: تواصل عبر البوت.")
    return "\n".join(lines)

def get_payment_text() -> str:
    return (
        "💳 طرق الدفع المتاحة:\n"
        "- تحويل بنكي/فوري.\n"
        "- الدفع عند الاستلام (مع دفع مصاريف الشحن مقدماً).\n"
        "- للدفع الإلكتروني والتفاصيل، تواصل مع فريق المبيعات عبر البوت."
    )

def get_installment_text() -> str:
    return (
        "💳 معلومات التقسيط:\n"
        "- التقسيط متاح عبر فيزا مشتريات أي بنك، أمان، ڤاليو.\n"
        "- الشروط والتفاصيل تختلف حسب البنك وخطة التقسيط، لذلك تواصل مع المبيعات عبر البوت للحصول على عرض مخصص."
    )

def get_marketing_explainer() -> str:
    groups = [
        "1- https://www.facebook.com/groups/1972887159552259/?ref=share&mibextid=NSMWBT",
        "2- https://www.facebook.com/groups/iphonecommunityegypt/?ref=share&mibextid=NSMWBT",
        "3- https://www.facebook.com/groups/218190547017878/?ref=share&mibextid=NSMWBT",
        "4- https://www.facebook.com/groups/egyptmsd/?ref=share&mibextid=NSMWBT",
        "5- https://www.facebook.com/groups/103248430087464/?ref=share&mibextid=NSMWBT",
        "6- https://www.facebook.com/groups/3525598867468502/?ref=share&mibextid=NSMWBT",
        "7- https://www.facebook.com/groups/3132532350314445/?ref=share&mibextid=NSMWBT",
        "8- https://www.facebook.com/groups/2457139851202815/?ref=share&mibextid=NSMWBT",
        "9- https://www.facebook.com/groups/2411054702443933/?ref=share&mibextid=NSMWBT",
        "10- https://www.facebook.com/groups/2268849979996851/?ref=share&mibextid=NSMWBT",
        "11- https://www.facebook.com/groups/1923852207852691/?ref=share&mibextid=NSMWBT",
        "12- https://www.facebook.com/groups/1805231006193013/?ref=share&mibextid=NSMWBT",
        "13- https://www.facebook.com/groups/1865494127042147/?ref=share&mibextid=NSMWBT",
        "14- https://www.facebook.com/groups/1795287180651452/?ref=share&mibextid=NSMWBT",
        "15- https://www.facebook.com/groups/1781972468862622/?ref=share&mibextid=NSMWBT",
        "16- https://www.facebook.com/groups/1770331939938628/?ref=share&mibextid=NSMWBT",
        "17- https://www.facebook.com/groups/appleusersbd/?ref=share&mibextid=NSMWBT",
        "18- https://www.facebook.com/groups/1650537338640529/?ref=share&mibextid=NSMWBT",
        "19- https://www.facebook.com/groups/1638757049766526/?ref=share&mibextid=NSMWBT",
        "20- https://www.facebook.com/groups/1608720326173557/?ref=share&mibextid=NSMWBT",
        "21- https://www.facebook.com/groups/1527692164177111/?ref=share&mibextid=NSMWBT",
        "22- https://www.facebook.com/groups/1493169271425469/?ref=share&mibextid=NSMWBT",
        "23- https://www.facebook.com/groups/petermacdoctor/?ref=share&mibextid=NSMWBT",
        "24- https://www.facebook.com/groups/1458211671065713/?ref=share&mibextid=NSMWBT",
        "25- https://www.facebook.com/groups/1433264563620939/?ref=share&mibextid=NSMWBT",
        "26- https://www.facebook.com/groups/1313706005705901/?ref=share&mibextid=NSMWBT",
        "27- https://www.facebook.com/groups/1170660230136490/?ref=share&mibextid=NSMWBT",
    ]
    advice = (
        "شرح طريقة الشغل \n\n"
        "نظامنا التسويق بالعمولة لأجهزة اللابتوب، وبنختص بأجهزة MacBook. "
        "المسوق يضع العمولة التي يريدها فوق إجمالي سعر الجهاز، وبالتالي يظهر السعر النهائي عند العرض.\n\n"
        "مثال: لو الجهاز سعره 10,000 ج.م والمسوق حاطط عمولة 200 ج.م — بيعرض الجهاز بسعر 10,200 ج.م.\n\n"
        "أين ننشر الإعلانات؟\n"
        "- ننزل الإعلانات في الـ Marketplace ومجموعات فيسبوك المتخصصة بتسويق اللابتوبات وأجهزة ماك.\n"
        "- المجموعات المقترحة (ابدأ بها للمسوقين الجدد):\n" + "\n".join(groups) + "\n\n"
        "نصائح مهمة:\n"

        "فيديو تفصيلي لشرح تنزيل الإعلان ونصائح عملية (مهم جداً للمسوقين الجدد):\n"
        "https://t.me/apple_planet_eg/78?single\n\n"
        "لأي استفسار إضافي تواصل عبر البوت: " + BOT_HANDLE
    )
    return advice

def get_contact_text() -> str:
    return f"للطلب أو الاستفسار تواصل معنا عبر البوت: {BOT_HANDLE}\n\n(إذا لم تتم إضافة سؤالك في القائمة سيتم تحويل رسالتك لفريق الدعم وسيتم الرد عليك.)"

# مساعد لتحويل مدخل المستخدم (رقم/ايموجي/كلمة) إلى خيار
def interpret_user_input(text: str) -> str:
    t = text.strip()
    # تحويل إيموجي أو أرقام عربية إلى أرقام إنجليزية
    t_simple = t.replace("١", "1").replace("٢", "2").replace("٣", "3").replace("٤", "4").replace("٥", "5")\
        .replace("٦", "6").replace("٧", "7").replace("٨", "8").replace("٩", "9").replace("٠", "0")
    # بعض الإيموجي الشائعة
    t_simple = t_simple.replace("1️⃣", "1").replace("2️⃣", "2").replace("3️⃣", "3").replace("4️⃣", "4").replace("5️⃣", "5")
    t_lower = t_simple.lower()

    if t_lower in ["1", "شحن", "shipping"]:
        return "shipping"
    if t_lower in ["2", "الضمان", "ضمان", "warranty"]:
        return "warranty"
    if t_lower in ["3", "الدفع", "pay", "payment"]:
        return "payment"
    if t_lower in ["4", "التقسيط", "تقسيط", "installment"]:
        return "installment"
    if t_lower in ["5", "شرح", "طريقة الشغل", "كيفية العمل", "شرح طريقة الشغل"]:
        return "marketing"
    if t_lower in ["6", "تواصل", "اتصال", "contact"]:
        return "contact"

    # كلمات مفتاحية عامة
    if "شحن" in t_lower:
        return "shipping"
    if "ضمان" in t_lower:
        return "warranty"
    if "تقسيط" in t_lower:
        return "installment"
    if "دفع" in t_lower or "تحويل" in t_lower:
        return "payment"
    if "شرح" in t_lower or "اعلان" in t_lower or "طريقة" in t_lower:
        return "marketing"

    return "unknown"

# ===== معالج رسائل الخاص (الردود المرتبة، وفورورد للادمن) =======
async def faq_handler(update: Update, context: ContextTypes.DEFAULT_TYPE):
    if update.effective_chat.type != "private":
        return

    # إذا المرسل هو الأدمين: سنتعامل في handler خاص أدناه
    if update.effective_user.id == ADMIN_ID:
        # لا نتعامل هنا مع رسائل الادمن
        return

    text = update.message.text or ""
    choice = interpret_user_input(text)

    if choice == "shipping":
        await update.message.reply_text(get_shipping_text())
        return
    if choice == "warranty":
        await update.message.reply_text(get_warranty_text())
        return
    if choice == "payment":
        await update.message.reply_text(get_payment_text())
        return
    if choice == "installment":
        await update.message.reply_text(get_installment_text())
        return
    if choice == "marketing":
        await update.message.reply_text(get_marketing_explainer())
        return
    if choice == "contact":
        await update.message.reply_text(get_contact_text())
        return

    # ===== التصحيح هنا =====
    # الرسالة الجديدة مع التصحيح
    response_text = (
        "للأسف مقدرتش افهم إستفسارك 🙏\n\n"
        "برجاء إدخال رقم سؤال من الأسئلة المقترحة أو إذا كان لديك سؤال أخر "
        "برجاء إرساله إلي فريق خدمة العملاء علي الواتساب من خلال الرابط التالي:\n\n"
        "https://wa.me/201094383927"
    )

    await update.message.reply_text(response_text)

    # إعادة توجيه الرسالة الأصلية للادمن والاحتفاظ بخريطة id لمتابعة الرد
    try:
        # استخدم copy_message بدلاً من forward_message للحفاظ على التنسيق
        forwarded = await context.bot.copy_message(
            chat_id=ADMIN_ID,
            from_chat_id=update.effective_chat.id,
            message_id=update.message.message_id
        )
        # خزن العلاقة بين id الرسالة المعاد توجيهها والـ chat_id الأصلي
        admin_forward_map[forwarded.message_id] = update.effective_user.id
    except Exception as e:
        # لو فشل الفوروورد: أرسل نص للادمين
        try:
            await context.bot.send_message(chat_id=ADMIN_ID, text=f"فشل إعادة توجيه رسالة من {update.effective_user.id}:\n{text}\n\nخطأ: {e}")
        except Exception:
            pass

# ===== معالج رسائل الادمن (الرد على الرسائل المعاد توجيهها) =======
async def admin_handler(update: Update, context: ContextTypes.DEFAULT_TYPE):
    # هذا المعالج يتعامل فقط مع الادمن
    if update.effective_user.id != ADMIN_ID:
        return

    msg = update.message

    # إذا عمل الأدمين ريبلاي على رسالة معاد توجيهها، أرسل الرد للمستخدم الأصلي
    if msg.reply_to_message:
        replied_id = msg.reply_to_message.message_id
        original_chat_id = admin_forward_map.get(replied_id)

        if original_chat_id:
            # أرسل النص (أو الملصق/وسائل أخرى لو حبيت تطور لاحقًا)
            if msg.text:
                try:
                    await context.bot.send_message(
                        chat_id=original_chat_id, 
                        text=f"📩 رد من فريق الدعم:\n\n{msg.text}"
                    )
                    await msg.reply_text("✅ تم إرسال الرد للعميل.")
                except Exception as e:
                    await msg.reply_text(f"⚠️ فشل إرسال الرد للعميل: {e}")
            e
