<?php
session_start();
require_once __DIR__ . '/../config/app.php';
require_once ROOT_PATH . 'config/db.php';

/* ===================== FETCH MOVIE ===================== */
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare($id ? "SELECT * FROM movies WHERE id = ?" : "SELECT * FROM movies ORDER BY created_at DESC LIMIT 1");
$stmt->execute($id ? [$id] : []);
$movie = $stmt->fetch();

if (!$movie) {
    die("<h2 style='text-align:center;padding:50px;color:white;background:#111;min-height:100vh'>No movies in database.</h2>");
}

$movie_id = $movie['id'];

/* ===================== RATING DATA ===================== */
$rating = $pdo->prepare("SELECT AVG(rating) AS avg_rating, COUNT(*) AS total FROM comments WHERE movie_id=?");
$rating->execute([$movie_id]);
$rate = $rating->fetch();
$ratingAvg = $rate['avg_rating'] ? round($rate['avg_rating'], 1) : 0;
$ratingCount = $rate['total'] ?? 0;

/* ===================== RESUME WATCH ===================== */
$lastTime = 0;
if (isset($_SESSION['user_id'])) {
    $u = $_SESSION['user_id'];
    $res = $pdo->prepare("SELECT last_time FROM history WHERE user_id=? AND movie_id=?");
    $res->execute([$u, $movie_id]);
    $row = $res->fetch();
    $lastTime = $row['last_time'] ?? 0;

    $pdo->prepare("INSERT IGNORE INTO history (user_id,movie_id) VALUES(?,?)")->execute([$u, $movie_id]);
}

/* ===================== ADD VIEW ===================== */
$pdo->prepare("UPDATE movies SET views=views+1 WHERE id=?")->execute([$movie_id]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($movie['title']) ?> | MovieBox</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
:root{
    --red:#E50914;--dark:#0C0C0C;--card:#141414;
}
body{margin:0;font-family:"Poppins",sans-serif;background:var(--dark);color:white;}
.page-bg{
    background:url('<?= BASE_URL ?>uploads/posters/<?=htmlspecialchars($movie['poster'])?>') center/cover;
    filter:blur(30px) brightness(0.4);
    position:fixed;left:0;top:0;width:100%;height:100%;z-index:-1;
}
.container{max-width:1100px;margin:auto;padding:20px;}
.video-box{background:black;border-radius:15px;overflow:hidden;box-shadow:0 10px 40px rgba(0,0,0,.6);}
video{width:100%;display:block;}
.title{margin-top:20px;font-size:2.3rem;font-weight:700;}
.tags{display:flex;gap:10px;font-size:0.9rem;margin:10px 0;color:#ccc;flex-wrap:wrap}
.tags span{background:#222;padding:4px 10px;border-radius:5px;}
.rating-pill{background:#111;padding:5px 14px;border-radius:20px;}

.review-box{margin-top:40px;}
form{background:#111;padding:20px;border-radius:10px;margin-bottom:20px;}
textarea{width:100%;background:#0003;border:1px solid #333;color:white;padding:12px;border-radius:6px;}

.star-rating{display:flex;flex-direction:row-reverse;gap:5px;justify-content:flex-end;margin:10px 0;}
.star-rating input{display:none}
.star-rating label{font-size:28px;color:#444;cursor:pointer;transition:.2s}
.star-rating label:hover,
.star-rating input:checked ~ label,
.star-rating label:hover ~ label{color:var(--red)}

.btn{background:var(--red);border:none;color:white;padding:10px 20px;border-radius:6px;font-weight:600;cursor:pointer}

.comment{background:#111;padding:15px;border-radius:10px;margin-bottom:10px}
.comment .user{font-weight:600;font-size:0.9rem;}
.comment .text{color:#ddd;margin-top:5px;font-size:0.9rem}
.stars{color:#ffc107;margin-left:auto;}

@media(max-width:768px){
 .title{font-size:1.6rem;text-align:center;}
 .container{padding:10px;}
}
</style>
</head>

<body>
<div class="page-bg"></div>

<?php include __DIR__ . '/../users/navibar.php'; ?>

<div class="container">

<div class="video-box">
<video id="player" controls controlsList="nodownload">
<source src="<?= BASE_URL ?>uploads/videos/<?=$movie['video']?>" type="video/mp4">
</video>
</div>

<h1 class="title"><?=htmlspecialchars($movie['title'])?></h1>
<div class="tags">
<span><?=$movie['year']?></span>
<span><?=$movie['genre']?></span>
<span><?=$movie['language']?></span>
<span class="rating-pill"><i class="fa fa-star"></i> <?=$ratingAvg?> (<?=$ratingCount?>)</span>
<span><i class="fa fa-eye"></i> <?=number_format($movie['views'])?> views</span>
</div>

<div class="review-box">
<h2>User Reviews</h2>

<?php if(isset($_SESSION['user_id'])): ?>
<form id="reviewForm">
<input type="hidden" name="movie_id" value="<?=$movie_id?>">

<div class="star-rating">
<?php for($i=5;$i>=1;$i--): ?>
<input type="radio" id="rate<?=$i?>" name="rating" value="<?=$i?>" required>
<label for="rate<?=$i?>" class="fa fa-star"></label>
<?php endfor; ?>
</div>

<textarea name="comment" rows="3" placeholder="Your review..." required></textarea>
<button class="btn" type="submit">Submit</button>
<div id="msg" style="margin-top:10px;font-size:0.9rem;"></div>
</form>
<?php else: ?>
<p><a href="<?= BASE_URL ?>auth/login.php" style="color:var(--red)">Login</a> to post a review.</p>
<?php endif; ?>

<div id="commentsArea">
<?php
$stmt=$pdo->prepare("SELECT c.*,u.username FROM comments c JOIN users u ON u.id=c.user_id WHERE movie_id=? ORDER BY created_at DESC");
$stmt->execute([$movie_id]);
foreach($stmt as $c): ?>
<div class="comment">
<div style="display:flex;justify-content:space-between;">
<span class="user"><?=htmlspecialchars($c['username'])?></span>
<span class="stars"><?=str_repeat("★",$c['rating']).str_repeat("☆",5-$c['rating'])?></span>
</div>
<div class="text"><?=htmlspecialchars($c['comment'])?></div>
</div>
<?php endforeach; ?>
</div>
</div>
</div>

<script>
/* ========== RESUME VIDEO ========== */
const v=document.getElementById("player");
v.currentTime = <?=$lastTime?>;

/* SAVE WATCH PROGRESS */
let lastSave=0;
v.addEventListener("timeupdate",()=>{
 let t=Math.floor(v.currentTime);
 if(t%12===0 && t!==lastSave){
  lastSave=t;
  fetch("<?= BASE_URL ?>users/save_progress.php",{
     method:"POST",
     headers:{"Content-Type":"application/json"},
     body:JSON.stringify({movie_id:<?=$movie_id?>,current_time:v.currentTime})
  });
 }
});

/* ========== AJAX COMMENT POST ========== */
document.getElementById("reviewForm")?.addEventListener("submit",async e=>{
 e.preventDefault();
 const fd=new FormData(e.target);
 const msg=document.getElementById("msg");
 msg.innerHTML="Posting...";
  let res=await fetch("<?= BASE_URL ?>users/add_comment.php",{method:"POST",body:fd});
 if(res.ok){
     msg.innerHTML="⭐ Review added!";
     // reload only comments + rating
     updateComments();
 }else msg.innerHTML="❌ Error! Try again.";
});

async function updateComments(){
  let r=await fetch("<?= BASE_URL ?>movies/get_comments.php?movie=<?=$movie_id?>");
 let html=await r.text();
 document.getElementById("commentsArea").innerHTML=html;
}
</script>

<?php include __DIR__."/../users/footer.php"; ?>
</body>
</html>
