<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP OOP Project Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --border-color: rgba(255, 255, 255, 0.1);
            --primary: #6366f1;
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            --accent-green: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --accent-orange: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            --text-light: #f8fafc;
            --text-sub: #94a3b8;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Prompt', 'Outfit', sans-serif;
            background-color: var(--bg-dark);
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(168, 85, 247, 0.15) 0px, transparent 50%);
            color: var(--text-light);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .container {
            max-width: 1000px;
            width: 100%;
        }

        .header {
            text-align: center;
            margin-bottom: 3rem;
            animation: fadeInDown 0.8s ease-out;
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: var(--text-sub);
            font-size: 1.1rem;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 1.8rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--primary-gradient);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .card.green::before { background: var(--accent-green); }
        .card.orange::before { background: var(--accent-orange); }

        .card:hover {
            transform: translateY(-6px);
            border-color: rgba(255, 255, 255, 0.25);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.4), 0 8px 10px -6px rgba(0, 0, 0, 0.3);
        }

        .card:hover::before {
            opacity: 1;
        }

        .card-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.2rem;
            font-size: 1.5rem;
        }

        .card h2 {
            font-size: 1.35rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .card p {
            color: var(--text-sub);
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 1.5rem;
            flex-grow: 1;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            background: rgba(99, 102, 241, 0.15);
            color: #818cf8;
            width: fit-content;
        }

        .card.green .badge {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
        }

        .card.orange .badge {
            background: rgba(249, 115, 22, 0.15);
            color: #fb923c;
        }

        .footer {
            margin-top: 3rem;
            color: var(--text-sub);
            font-size: 0.9rem;
            text-align: center;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PHP OOP Project Portal</h1>
            <p>เลือกระบบที่ต้องการเข้าใช้งานด้านล่าง</p>
        </div>

        <div class="grid">
            <a href="ทีมพัฒนาแอป035-036/index.php" class="card">
                <div class="card-icon">🚀</div>
                <h2>SpaceHub Booking</h2>
                <p>ระบบจองห้องประชุมและ Co-Working Space อัจฉริยะ (ทีมพัฒนาแอป 035-036)</p>
                <span class="badge">เข้าใช้งานระบบ &rarr;</span>
            </a>

            <a href="miniproject/index.php" class="card green">
                <div class="card-icon">🖨️</div>
                <h2>Printing Service</h2>
                <p>ระบบคำนวณราคาและสั่งพิมพ์งาน/เข้าเล่มดิจิทัล พร้อมระบบบันทึกประวัติใบเสร็จ</p>
                <span class="badge">เข้าใช้งานระบบ &rarr;</span>
            </a>

            <a href="section.html" class="card orange">
                <div class="card-icon">📖</div>
                <h2>OOP Documentation</h2>
                <p>เอกสารประกอบการเรียนและคู่มือเชิงแนวคิดเชิงวัตถุ (Constructors & Methods)</p>
                <span class="badge">ดูเอกสาร &rarr;</span>
            </a>
        </div>

        <div class="footer">
            <p>Running on PHP Development Server | Localhost:8000</p>
        </div>
    </div>
</body>
</html>
