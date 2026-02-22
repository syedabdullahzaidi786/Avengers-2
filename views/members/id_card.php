<?php
/**
 * Member ID Card Print View
 * Landscape Layout - Red/Dark System Theme
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Member.php';

if (!isset($_GET['id'])) {
    die('Member ID required');
}

$memberModel = new Member($pdo);
$member = $memberModel->getMemberById($_GET['id']);

if (!$member) {
    die('Member not found');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ID Card - <?php echo htmlspecialchars($member['full_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="<?php echo APP_URL; ?>/assets/js/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        :root {
            --primary: #ff4d4d;
            --primary-dark: #cc0000;
            --bg-dark: #121212;
            --card-dark: #1e1e1e;
            --text-light: #ffffff;
            --text-muted: rgba(255, 255, 255, 0.6);
        }
        body {
            background-color: #000;
            font-family: 'Inter', 'Segoe UI', sans-serif;
            color: var(--text-light);
            margin: 0;
            padding: 0;
        }
        .id-card-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .id-card {
            width: 580px;
            height: 350px;
            background: var(--card-dark);
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(255, 77, 77, 0.15);
            overflow: hidden;
            position: relative;
            display: flex;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Left Panel */
        .id-card-left {
            width: 220px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }
        .id-card-left::after {
            content: '';
            position: absolute;
            top: 0;
            right: -30px;
            bottom: 0;
            width: 60px;
            background: var(--card-dark);
            border-radius: 50% 0 0 50%;
        }
        .logo-img {
            max-height: 45px;
            margin-bottom: 25px;
            z-index: 2;
            filter: brightness(0) invert(1);
        }
        .member-photo {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            border: 4px solid var(--card-dark);
            background-color: #333;
            object-fit: cover;
            box-shadow: 0 8px 16px rgba(0,0,0,0.3);
            z-index: 2;
        }

        /* Right Panel */
        .id-card-right {
            flex: 1;
            padding: 30px 30px 30px 45px;
            display: flex;
            flex-direction: column;
            text-align: left;
            position: relative;
        }
        .member-name {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 2px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .member-role {
            color: var(--text-muted);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 20px;
        }
        .details-grid {
            margin-bottom: 10px;
        }
        .details-row {
            display: flex;
            margin-bottom: 8px;
            font-size: 0.85rem;
        }
        .details-label {
            color: var(--text-muted);
            font-weight: 500;
            width: 110px;
        }
        .details-value {
            color: var(--text-light);
            font-weight: 700;
        }
        .qr-code-wrap {
            position: absolute;
            bottom: 40px;
            right: 30px;
        }
        .qr-inner {
            padding: 6px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .id-card-footer {
            background: var(--primary);
            color: white;
            padding: 6px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: absolute;
            bottom: 0;
            width: 100%;
            text-align: center;
        }
        
        /* Actions */
        .actions {
            position: fixed;
            bottom: 30px;
            right: 30px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            z-index: 1000;
        }
        .action-btn {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        .action-btn:hover {
            transform: scale(1.1);
            color: white;
        }
        .btn-wa { background-color: #25d366; }
        .btn-print { background-color: #0d6efd; }
        .btn-down { background-color: var(--primary); }
        .btn-back {
            position: fixed;
            top: 25px;
            left: 25px;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            border: 1px solid rgba(255,255,255,0.1);
            z-index: 1000;
        }
        .btn-back:hover {
            background: rgba(255,255,255,0.2);
            color: white;
        }

        @media print {
            body { background: white; color: black !important; }
            .id-card-container { display: block; padding: 0; }
            .id-card { 
                box-shadow: none; 
                border: 1px solid #000; 
                margin: 20px auto; 
                color: black !important; 
                background: white !important;
                width: 86mm;
                height: 54mm;
            }
            .id-card-left { 
                background: var(--primary) !important; 
                -webkit-print-color-adjust: exact; 
                width: 35%;
            }
            .id-card-left::after { background: white !important; }
            .id-card-right { 
                background: white !important; 
                color: black !important; 
                padding: 15px 15px 15px 25px;
            }
            .member-name { color: var(--primary) !important; font-size: 1.2rem; }
            .details-value { color: black !important; }
            .details-label { color: #666 !important; width: 80px; }
            .id-card-footer { -webkit-print-color-adjust: exact; font-size: 0.6rem; }
            .qr-code-wrap { bottom: 25px; right: 15px; }
            .actions, .btn-back { display: none !important; }
            @page { size: landscape; margin: 0; }
        }
    </style>
</head>
<body>

<a href="generate_id.php" class="btn-back">
    <i class="fas fa-arrow-left me-2"></i> Back
</a>

<div class="id-card-container">
    <div class="id-card" id="idCard">
        <div class="id-card-left">
            <img src="<?php echo APP_URL; ?>/assets/images/logo.png" alt="Logo" class="logo-img" onerror="this.style.display='none';this.nextElementSibling.style.display='block'"><span style="display:none;font-size:1rem;font-weight:800;color:white;z-index:2;"><?php echo strtoupper(APP_NAME); ?></span>
            
            <?php
$defaultAvatar = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="150" height="150" viewBox="0 0 150 150"><rect width="150" height="150" fill="#555"/><circle cx="75" cy="55" r="30" fill="#888"/><ellipse cx="75" cy="130" rx="45" ry="35" fill="#888"/></svg>');
$photoUrl = $member['profile_picture'] ? APP_URL . '/' . $member['profile_picture'] : $defaultAvatar;
?>
            <img src="<?php echo $photoUrl; ?>" alt="Profile" class="member-photo" onerror="this.src='<?php echo $defaultAvatar; ?>'">
            
            <div style="z-index: 2; margin-top: 15px; font-weight: 800; font-size: 0.9rem; letter-spacing: 1px; color: white;">MEMBER</div>
        </div>
        
        <div class="id-card-right">
            <div class="member-name"><?php echo htmlspecialchars($member['full_name']); ?></div>
            <div class="member-role">GYM MEMBER</div>
            
            <div class="details-grid">
                <div class="details-row">
                    <span class="details-label">ID NO:</span>
                    <span class="details-value">#<?php echo str_pad($member['id'], 6, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="details-row">
                    <span class="details-label">PHONE:</span>
                    <span class="details-value"><?php echo htmlspecialchars($member['phone']); ?></span>
                </div>
                <div class="details-row">
                    <span class="details-label">PLAN:</span>
                    <span class="details-value"><?php echo htmlspecialchars($member['plan_name']); ?></span>
                </div>
                <?php if ($member['trainer_id']): ?>
                <div class="details-row">
                    <span class="details-label">TRAINER:</span>
                    <span class="details-value"><?php echo htmlspecialchars($member['trainer_name']); ?></span>
                </div>
                <div class="details-row">
                    <span class="details-label">SPECIALTY:</span>
                    <span class="details-value"><?php echo htmlspecialchars($member['specialization'] ?? 'General Fitness'); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <div class="qr-code-wrap">
                <div class="qr-inner">
                    <div id="qrcode"></div>
                </div>
            </div>
            
            <div class="id-card-footer">
                <?php echo strtoupper(APP_NAME); ?>
            </div>
        </div>
    </div>
</div>

<div class="actions">
    <button class="action-btn btn-down" onclick="downloadCard()" title="Download as Image">
        <i class="fas fa-download fa-lg"></i>
    </button>
    
    <?php
$whatsappText = "Asalam-O-Alikum " . $member['full_name'] . ", your " . APP_NAME . " ID Card is ready!\n\nMembership ID: " . str_pad($member['id'], 6, '0', STR_PAD_LEFT) . "\nExpiry Date: " . date('d M Y', strtotime($member['end_date'])) . "\n\nPlease download and keep this card with you.";
$whatsappUrl = "https://wa.me/" . preg_replace('/[^0-9]/', '', $member['phone']) . "?text=" . urlencode($whatsappText);
?>
    <a href="<?php echo $whatsappUrl; ?>" target="_blank" class="action-btn btn-wa" title="Share Message on WhatsApp">
        <i class="fab fa-whatsapp fa-lg"></i>
    </a>
    
    <button class="action-btn btn-print" onclick="window.print()" title="Print Card">
        <i class="fas fa-print fa-lg"></i>
    </button>
</div>

<script>
    // Generate QR Code
    var qrcode = new QRCode(document.getElementById("qrcode"), {
        text: "<?php echo $member['id']; ?>",
        width: 70,
        height: 70,
        colorDark : "#000000",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.H
    });

    // Download Card as Image
    function downloadCard() {
        const card = document.getElementById("idCard");
        const btn = document.querySelector('.btn-down');
        
        // Disable button and show spinner
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin fa-lg"></i>';
        
        console.log('Starting ID Card capture...');
        
        html2canvas(card, {
            useCORS: true,
            allowTaint: true,
            scale: 3, // Higher quality
            backgroundColor: null,
            logging: true,
            letterRendering: true
        }).then(canvas => {
            console.log('Canvas generated successfully');
            try {
                const imgData = canvas.toDataURL("image/png");
                const link = document.createElement('a');
                link.download = 'ID_Card_<?php echo $member['id']; ?>.png';
                link.href = imgData;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                console.log('Download triggered');
            } catch (e) {
                console.error('Error generating image data:', e);
                alert('Could not generate image. This might be due to security restrictions on the profile photo. Try printing as PDF instead.');
            }
            
            // Restore button
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-download fa-lg"></i>';
        }).catch(err => {
            console.error('Capture failed:', err);
            alert('ID Card capture failed. Please check the console for details.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-download fa-lg"></i>';
        });
    }
</script>

</body>
</html>
