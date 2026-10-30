"""
Generate Vyapar Setu Multi-Vendor Marketplace presentation (11 slides).
Run: python generate_presentation.py
"""

from pathlib import Path

from pptx import Presentation
from pptx.dml.color import RGBColor
from pptx.enum.text import MSO_ANCHOR, PP_ALIGN
from pptx.util import Inches, Pt

BASE = Path(__file__).resolve().parent
SCREENSHOTS = BASE / "report-screenshots"
OUTPUT = BASE / "Vyapar_Setu_Website_Presentation.pptx"

# Brand palette
NAVY = RGBColor(0x1E, 0x1B, 0x4B)
PRIMARY = RGBColor(0x25, 0x63, 0xEB)
ORANGE = RGBColor(0xEA, 0x58, 0x0C)
GREEN = RGBColor(0x16, 0xA3, 0x4A)
WHITE = RGBColor(0xFF, 0xFF, 0xFF)
LIGHT = RGBColor(0xF1, 0xF5, 0xF9)
DARK_TEXT = RGBColor(0x1E, 0x29, 0x3B)
MUTED = RGBColor(0x64, 0x74, 0x8B)
ACCENT_BG = RGBColor(0xE0, 0xE7, 0xFF)


def set_slide_bg(slide, color: RGBColor) -> None:
    fill = slide.background.fill
    fill.solid()
    fill.fore_color.rgb = color


def add_header_bar(slide, title: str, subtitle: str = "") -> None:
    bar = slide.shapes.add_shape(1, Inches(0), Inches(0), Inches(13.333), Inches(1.15))
    bar.fill.solid()
    bar.fill.fore_color.rgb = NAVY
    bar.line.fill.background()

    tb = slide.shapes.add_textbox(Inches(0.6), Inches(0.18), Inches(12), Inches(0.55))
    tf = tb.text_frame
    tf.text = title
    p = tf.paragraphs[0]
    p.font.size = Pt(28)
    p.font.bold = True
    p.font.color.rgb = WHITE

    if subtitle:
        sb = slide.shapes.add_textbox(Inches(0.6), Inches(0.72), Inches(12), Inches(0.35))
        sf = sb.text_frame
        sf.text = subtitle
        sp = sf.paragraphs[0]
        sp.font.size = Pt(13)
        sp.font.color.rgb = RGBColor(0xCB, 0xD5, 0xE1)


def add_bullets(slide, items, left=0.75, top=1.45, width=11.8, height=5.5, size=18):
    box = slide.shapes.add_textbox(Inches(left), Inches(top), Inches(width), Inches(height))
    tf = box.text_frame
    tf.word_wrap = True
    for i, item in enumerate(items):
        p = tf.paragraphs[0] if i == 0 else tf.add_paragraph()
        p.text = item
        p.level = 0
        p.font.size = Pt(size)
        p.font.color.rgb = DARK_TEXT
        p.space_after = Pt(10)


def add_image_safe(slide, filename, left, top, width, height=None):
    path = SCREENSHOTS / filename
    if path.exists():
        if height:
            slide.shapes.add_picture(str(path), Inches(left), Inches(top), Inches(width), Inches(height))
        else:
            slide.shapes.add_picture(str(path), Inches(left), Inches(top), width=Inches(width))


def slide_title(prs):
    slide = prs.slides.add_slide(prs.slide_layouts[6])
    set_slide_bg(slide, NAVY)

    accent = slide.shapes.add_shape(1, Inches(0), Inches(3.8), Inches(13.333), Inches(0.12))
    accent.fill.solid()
    accent.fill.fore_color.rgb = ORANGE
    accent.line.fill.background()

    badge = slide.shapes.add_textbox(Inches(0.9), Inches(1.2), Inches(11), Inches(0.5))
    bf = badge.text_frame
    bf.text = "Multi-Vendor E-Commerce Platform"
    bp = bf.paragraphs[0]
    bp.font.size = Pt(16)
    bp.font.color.rgb = RGBColor(0x93, 0xC5, 0xFD)

    title = slide.shapes.add_textbox(Inches(0.9), Inches(1.75), Inches(11.5), Inches(1.4))
    tf = title.text_frame
    tf.text = "Vyapar Setu"
    p = tf.paragraphs[0]
    p.font.size = Pt(54)
    p.font.bold = True
    p.font.color.rgb = WHITE

    sub = title.text_frame.add_paragraph()
    sub.text = "Connecting Indian SMEs & Customers on One Digital Marketplace"
    sub.font.size = Pt(22)
    sub.font.color.rgb = RGBColor(0xE2, 0xE8, 0xF0)
    sub.space_before = Pt(8)

    meta = slide.shapes.add_textbox(Inches(0.9), Inches(4.35), Inches(11), Inches(1.2))
    mf = meta.text_frame
    mf.text = "Academic / Project Presentation"
    mp = mf.paragraphs[0]
    mp.font.size = Pt(14)
    mp.font.color.rgb = MUTED
    mp2 = mf.add_paragraph()
    mp2.text = "PHP • MySQL • Bootstrap 5 • JavaScript • AJAX"
    mp2.font.size = Pt(13)
    mp2.font.color.rgb = RGBColor(0x94, 0xA3, 0xB8)
    mp2.space_before = Pt(6)


def slide_about(prs):
    slide = prs.slides.add_slide(prs.slide_layouts[6])
    set_slide_bg(slide, WHITE)
    add_header_bar(slide, "About the Project", "What is Vyapar Setu?")
    add_bullets(
        slide,
        [
            "Vyapar Setu is a full-stack Multi-Vendor Marketplace web application.",
            "It enables multiple independent sellers (SMEs) to list and sell products on a shared platform.",
            "Customers can browse categories, compare prices, add to cart, and checkout securely.",
            "Built for the Indian market — supports UPI, COD, GST-ready vendor onboarding, and regional categories.",
            "Categories include Electronics, Fashion, Spices & Organics, Home & Living, Beauty & Wellness, and more.",
            "Includes AI-powered chatbot assistance for product discovery and customer support.",
        ],
        size=17,
    )


def slide_objectives(prs):
    slide = prs.slides.add_slide(prs.slide_layouts[6])
    set_slide_bg(slide, WHITE)
    add_header_bar(slide, "Project Objectives", "Goals & Problem Statement")
    add_bullets(
        slide,
        [
            "Provide a scalable digital storefront for small and medium Indian businesses.",
            "Offer role-based portals for Customers, Vendors, and Administrators.",
            "Automate product listing, inventory tracking, order fulfillment, and payments.",
            "Deliver a modern, mobile-responsive shopping experience with real-time cart updates.",
            "Ensure secure authentication, SQL injection protection, and password hashing.",
            "Support analytics dashboards for vendors and platform-wide GMV reporting for admins.",
        ],
        size=17,
    )


def slide_tech_stack(prs):
    slide = prs.slides.add_slide(prs.slide_layouts[6])
    set_slide_bg(slide, WHITE)
    add_header_bar(slide, "Technology Stack", "Tools & frameworks used")

    cards = [
        ("Frontend", "HTML5, CSS3, Bootstrap 5, JavaScript, AJAX, Chart.js"),
        ("Backend", "PHP 8+ with modular includes & REST-style API endpoints"),
        ("Database", "MySQL (primary) with PDO — SQLite fallback for portability"),
        ("Security", "password_hash(), prepared statements, role-based access control"),
        ("Email / OTP", "PHPMailer for password reset & OTP verification flows"),
        ("Deployment", "XAMPP (Apache + MySQL) on localhost / Windows environment"),
    ]

    y = 1.55
    for title, desc in cards:
        rect = slide.shapes.add_shape(1, Inches(0.75), Inches(y), Inches(11.8), Inches(0.72))
        rect.fill.solid()
        rect.fill.fore_color.rgb = LIGHT
        rect.line.color.rgb = RGBColor(0xE2, 0xE8, 0xF0)

        tb = slide.shapes.add_textbox(Inches(0.95), Inches(y + 0.1), Inches(11.4), Inches(0.55))
        tf = tb.text_frame
        tf.text = title
        p = tf.paragraphs[0]
        p.font.bold = True
        p.font.size = Pt(15)
        p.font.color.rgb = PRIMARY
        p2 = tf.add_paragraph()
        p2.text = desc
        p2.font.size = Pt(13)
        p2.font.color.rgb = DARK_TEXT
        y += 0.82


def slide_user_roles(prs):
    slide = prs.slides.add_slide(prs.slide_layouts[6])
    set_slide_bg(slide, WHITE)
    add_header_bar(slide, "System Architecture", "Three user roles & their capabilities")

    roles = [
        ("Customer", GREEN, [
            "Register / login with email & OTP verification",
            "Browse shop, search, filter by category & price",
            "Cart, wishlist, multi-step checkout (UPI / COD)",
            "Order tracking, reviews, address management",
        ]),
        ("Vendor (SME)", ORANGE, [
            "Dedicated vendor login & store dashboard",
            "Add / edit products with photo upload",
            "Manage inventory, orders, returns & coupons",
            "Sales analytics, earnings & withdrawal requests",
        ]),
        ("Admin", PRIMARY, [
            "Platform governance & vendor approval workflow",
            "Manage users, categories, brands & products",
            "Order & payment oversight, banners & coupons",
            "GMV analytics, reports & system notifications",
        ]),
    ]

    x_positions = [0.55, 4.65, 8.75]
    for i, (role, color, points) in enumerate(roles):
        card = slide.shapes.add_shape(1, Inches(x_positions[i]), Inches(1.55), Inches(3.85), Inches(5.2))
        card.fill.solid()
        card.fill.fore_color.rgb = WHITE
        card.line.color.rgb = color

        hdr = slide.shapes.add_shape(1, Inches(x_positions[i]), Inches(1.55), Inches(3.85), Inches(0.65))
        hdr.fill.solid()
        hdr.fill.fore_color.rgb = color
        hdr.line.fill.background()

        ht = slide.shapes.add_textbox(Inches(x_positions[i] + 0.15), Inches(1.68), Inches(3.55), Inches(0.4))
        hf = ht.text_frame
        hf.text = role
        hp = hf.paragraphs[0]
        hp.font.bold = True
        hp.font.size = Pt(16)
        hp.font.color.rgb = WHITE
        hp.alignment = PP_ALIGN.CENTER

        bt = slide.shapes.add_textbox(Inches(x_positions[i] + 0.2), Inches(2.35), Inches(3.45), Inches(4.2))
        bf = bt.text_frame
        bf.word_wrap = True
        for j, pt in enumerate(points):
            para = bf.paragraphs[0] if j == 0 else bf.add_paragraph()
            para.text = f"• {pt}"
            para.font.size = Pt(12)
            para.font.color.rgb = DARK_TEXT
            para.space_after = Pt(8)


def slide_homepage(prs):
    slide = prs.slides.add_slide(prs.slide_layouts[6])
    set_slide_bg(slide, WHITE)
    add_header_bar(slide, "Storefront — Homepage", "Hero carousel, categories & trending products")

    add_bullets(
        slide,
        [
            "Independence Day sale hero carousel with promotional banners",
            "Category grid with icons (Electronics, Fashion, Spices, etc.)",
            "Trending & featured product cards with ratings & vendor info",
            "Fully responsive navbar with search, cart badge & wishlist",
        ],
        left=0.75,
        top=1.4,
        width=4.8,
        height=4.5,
        size=14,
    )
    add_image_safe(slide, "ppt_homepage_trending.png", 5.55, 1.35, 7.0, 5.0)


def slide_shop(prs):
    slide = prs.slides.add_slide(prs.slide_layouts[6])
    set_slide_bg(slide, WHITE)
    add_header_bar(slide, "Product Catalog & Shop", "Browse, filter & discover products")

    add_image_safe(slide, "ppt_shop_catalog.png", 0.6, 1.35, 7.2, 5.0)
    add_bullets(
        slide,
        [
            "Dedicated shop page with grid/list product layout",
            "Filter by category, price range & spotlight tags",
            "Product detail pages with images, description & reviews",
            "Add to cart & wishlist via AJAX — no page reload",
            "8+ vendor stores across diverse Indian product categories",
        ],
        left=8.1,
        top=1.5,
        width=4.6,
        height=4.8,
        size=14,
    )


def slide_categories(prs):
    slide = prs.slides.add_slide(prs.slide_layouts[6])
    set_slide_bg(slide, WHITE)
    add_header_bar(slide, "Product Categories", "Diverse marketplace inventory")

    images = [
        ("ppt_electronics.png", "Electronics", 0.55, 1.45),
        ("ppt_fashion.png", "Fashion", 3.55, 1.45),
        ("ppt_spices.png", "Spices & Organics", 6.55, 1.45),
        ("ppt_beauty.png", "Beauty & Wellness", 9.55, 1.45),
        ("ppt_home_living.png", "Home & Living", 3.55, 4.15),
    ]

    for fname, label, x, y in images:
        add_image_safe(slide, fname, x, y, 2.75, 2.35)
        lb = slide.shapes.add_textbox(Inches(x), Inches(y + 2.42), Inches(2.75), Inches(0.35))
        lf = lb.text_frame
        lf.text = label
        lp = lf.paragraphs[0]
        lp.font.size = Pt(11)
        lp.font.bold = True
        lp.font.color.rgb = NAVY
        lp.alignment = PP_ALIGN.CENTER


def slide_customer_features(prs):
    slide = prs.slides.add_slide(prs.slide_layouts[6])
    set_slide_bg(slide, WHITE)
    add_header_bar(slide, "Customer Experience", "Shopping journey end-to-end")

    features = [
        ("Registration & Login", "Email/password auth, OTP verification, forgot-password flow"),
        ("Shopping Cart", "Real-time cart badge, quantity updates, persistent session cart"),
        ("Checkout & Payment", "Multi-step checkout with Razorpay/UPI simulation & Cash on Delivery"),
        ("Order Management", "Order confirmation, tracking status, returns & review submission"),
        ("AI Chatbot", "Intelligent assistant for product recommendations & FAQs"),
        ("Wishlist", "Save favourite products and toggle via AJAX from any page"),
    ]

    y = 1.55
    for title, desc in features:
        dot = slide.shapes.add_shape(1, Inches(0.8), Inches(y + 0.08), Inches(0.18), Inches(0.18))
        dot.fill.solid()
        dot.fill.fore_color.rgb = GREEN
        dot.line.fill.background()

        tb = slide.shapes.add_textbox(Inches(1.15), Inches(y), Inches(11.2), Inches(0.65))
        tf = tb.text_frame
        tf.text = title
        p = tf.paragraphs[0]
        p.font.bold = True
        p.font.size = Pt(15)
        p.font.color.rgb = NAVY
        p2 = tf.add_paragraph()
        p2.text = desc
        p2.font.size = Pt(13)
        p2.font.color.rgb = MUTED
        y += 0.88


def slide_vendor_admin(prs):
    slide = prs.slides.add_slide(prs.slide_layouts[6])
    set_slide_bg(slide, WHITE)
    add_header_bar(slide, "Vendor & Admin Portals", "Business management dashboards")

    # Vendor column
    vbox = slide.shapes.add_shape(1, Inches(0.65), Inches(1.5), Inches(5.9), Inches(5.3))
    vbox.fill.solid()
    vbox.fill.fore_color.rgb = ACCENT_BG
    vbox.line.color.rgb = PRIMARY

    vt = slide.shapes.add_textbox(Inches(0.9), Inches(1.65), Inches(5.4), Inches(0.4))
    vtf = vt.text_frame
    vtf.text = "Vendor Dashboard"
    vtp = vtf.paragraphs[0]
    vtp.font.bold = True
    vtp.font.size = Pt(18)
    vtp.font.color.rgb = PRIMARY

    vendor_pts = [
        "Add products with PC/mobile photo upload",
        "Inventory & stock management",
        "Process orders & handle returns",
        "Analytics charts & earnings reports",
        "Coupons, messages & customer reviews",
    ]
    vb = slide.shapes.add_textbox(Inches(0.95), Inches(2.15), Inches(5.3), Inches(4.5))
    vbf = vb.text_frame
    for i, pt in enumerate(vendor_pts):
        para = vbf.paragraphs[0] if i == 0 else vbf.add_paragraph()
        para.text = f"• {pt}"
        para.font.size = Pt(14)
        para.font.color.rgb = DARK_TEXT
        para.space_after = Pt(6)

    # Admin column
    abox = slide.shapes.add_shape(1, Inches(6.75), Inches(1.5), Inches(5.9), Inches(5.3))
    abox.fill.solid()
    abox.fill.fore_color.rgb = RGBColor(0xFF, 0xF7, 0xED)
    abox.line.color.rgb = ORANGE

    at = slide.shapes.add_textbox(Inches(7.0), Inches(1.65), Inches(5.4), Inches(0.4))
    atf = at.text_frame
    atf.text = "Admin Dashboard"
    atp = atf.paragraphs[0]
    atp.font.bold = True
    atp.font.size = Pt(18)
    atp.font.color.rgb = ORANGE

    admin_pts = [
        "Approve / manage vendor accounts",
        "Moderate products, categories & brands",
        "Platform-wide orders & payment oversight",
        "Banner management & coupon control",
        "GMV analytics, reports & user management",
    ]
    ab = slide.shapes.add_textbox(Inches(7.05), Inches(2.15), Inches(5.3), Inches(4.5))
    abf = ab.text_frame
    for i, pt in enumerate(admin_pts):
        para = abf.paragraphs[0] if i == 0 else abf.add_paragraph()
        para.text = f"• {pt}"
        para.font.size = Pt(14)
        para.font.color.rgb = DARK_TEXT
        para.space_after = Pt(6)


def slide_conclusion(prs):
    slide = prs.slides.add_slide(prs.slide_layouts[6])
    set_slide_bg(slide, NAVY)

    title = slide.shapes.add_textbox(Inches(0.9), Inches(0.9), Inches(11.5), Inches(0.8))
    tf = title.text_frame
    tf.text = "Conclusion & Future Scope"
    p = tf.paragraphs[0]
    p.font.size = Pt(32)
    p.font.bold = True
    p.font.color.rgb = WHITE

    items = [
        "Vyapar Setu successfully demonstrates a complete multi-vendor e-commerce ecosystem.",
        "Scalable architecture supports growing vendor base and product catalog.",
        "Future enhancements: live payment gateway, mobile app, real-time notifications, ML recommendations.",
        "Ready for deployment on XAMPP with MySQL — import schema & sample data to get started.",
    ]
    box = slide.shapes.add_textbox(Inches(0.9), Inches(1.85), Inches(11.2), Inches(2.8))
    bf = box.text_frame
    bf.word_wrap = True
    for i, item in enumerate(items):
        para = bf.paragraphs[0] if i == 0 else bf.add_paragraph()
        para.text = f"✓  {item}"
        para.font.size = Pt(16)
        para.font.color.rgb = RGBColor(0xE2, 0xE8, 0xF0)
        para.space_after = Pt(12)

    thanks = slide.shapes.add_textbox(Inches(0.9), Inches(5.0), Inches(11), Inches(1.2))
    thf = thanks.text_frame
    thf.text = "Thank You!"
    tp = thf.paragraphs[0]
    tp.font.size = Pt(40)
    tp.font.bold = True
    tp.font.color.rgb = ORANGE
    tp2 = thf.add_paragraph()
    tp2.text = "Questions & Demo: http://localhost/Multi_Vendor_Marketplace_final"
    tp2.font.size = Pt(14)
    tp2.font.color.rgb = MUTED
    tp2.space_before = Pt(10)


def main():
    prs = Presentation()
    prs.slide_width = Inches(13.333)
    prs.slide_height = Inches(7.5)

    slide_title(prs)
    slide_about(prs)
    slide_objectives(prs)
    slide_tech_stack(prs)
    slide_user_roles(prs)
    slide_homepage(prs)
    slide_shop(prs)
    slide_categories(prs)
    slide_customer_features(prs)
    slide_vendor_admin(prs)
    slide_conclusion(prs)

    prs.save(str(OUTPUT))
    print(f"Presentation saved: {OUTPUT}")
    print(f"Total slides: {len(prs.slides)}")


if __name__ == "__main__":
    main()
