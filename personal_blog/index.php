<?php
require_once __DIR__ . '/functions.php';
$category = isset($_GET['category']) ? sanitize_text($_GET['category']) : null;
$posts = get_all_posts($category ?: null);
include __DIR__ . '/templates/header.php';
?>
<section class="hero">
    <div>
        <div class="pill">Designer · Developer</div>
        <h1 class="title">Kian Garcia</h1>
        <p class="subtitle">Philippines born · Tokyo based<br>Photographer, UI/UX Designer</p>
        <blockquote class="hero-quote">My photography has no boundaries. Every time you press the shutter button, your unique vision is captured forever. That makes your work priceless.</blockquote>
    </div>
    <div>
        <div class="pill">🏆 IRISH MEDIA AWARDS</div>
        <h3 style="margin:12px 0 6px;">2022 / 2023 Photographer of the year</h3>
        <p class="meta" style="margin:0 0 12px;">Creative Design Department</p>
        <div class="pill">🎓 EDUCATION</div>
        <p style="margin:12px 0 6px; font-weight:700;">THE UNIVERSITY OF NORTH CAROLINA</p>
        <p class="meta" style="margin:0;">BS in Cognitive Science and CS, Dec 2020</p>
    </div>
</section>

<section class="section">
    <div class="grid-2">
        <div class="card">
            <div class="badge">BIO</div>
            <h2>About me</h2>
            <p class="sub">My name is Kian Garcia. I'm a 25 year-old photographer, UI/UX designer, developer and creative director. Born and raised in Japan, I moved to the USA in 2014, where I grew my passion for all things creative. I thrive in collaborative, fast-paced environments.</p>
        </div>
        <div class="highlight">
            <div class="pill" style="background: rgba(0,0,0,0.3); color: #fff;">FEATURED WORK</div>
            <h3>Vinemer Caves | 2012</h3>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla eget consequat justo, vitae laoreet leo.</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="grid-3">
        <div class="card">
            <img class="img-rounded" src="https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=800&q=80" alt="calm mountains">
            <h3>Photography Awards</h3>
            <p class="meta">KIAN GARCIA | 2022</p>
            <p>2022 IRISH MEDIA AWARDS / Photographer of the year<br>2021 NATIONAL GEOGRAPHIC CONTEST / Finalist</p>
        </div>
        <div class="card">
            <img class="img-rounded" src="https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=800&q=80" alt="education">
            <h3>Education</h3>
            <p class="meta">University of North Carolina</p>
            <p>BS in Cognitive Science and Computer Science.</p>
        </div>
        <div class="card">
            <img class="img-rounded" src="https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=800&q=80" alt="contact">
            <h3>Get in Touch</h3>
            <p class="meta">kian.garcia@fakeemail.com</p>
            <p>Based in Tokyo, available for freelance collaborations worldwide.</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="flex between center" style="gap:10px; flex-wrap:wrap;">
        <div>
            <h2><?php echo $category ? $category : 'Latest Posts'; ?></h2>
            <p class="sub">Recent tech and mood logs from the journey.</p>
        </div>
        <?php if ($category): ?>
            <a class="button secondary" href="/index.php">Clear Filter</a>
        <?php endif; ?>
    </div>
    <div class="post-grid">
        <?php foreach ($posts as $post): ?>
            <article class="card post-card">
                <div class="meta">
                    <span class="badge"><?php echo sanitize_text($post['category']); ?></span>
                    <span>• <?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                </div>
                <h3><a href="/view.php?id=<?php echo $post['id']; ?>"><?php echo sanitize_text($post['title']); ?></a></h3>
                <?php if (!empty($post['image_path'])): ?>
                    <img class="img-rounded" src="/<?php echo sanitize_text($post['image_path']); ?>" alt="cover">
                <?php endif; ?>
                <div class="meta">Updated: <?php echo date('M d, Y', strtotime($post['updated_at'])); ?></div>
                <?php if (is_logged_in()): ?>
                    <div class="flex between center" style="margin-top:12px;">
                        <a class="button secondary" href="/edit.php?id=<?php echo $post['id']; ?>">Edit</a>
                        <form action="/delete.php" method="POST" onsubmit="return confirm('Delete this post?');">
                            <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                            <button class="secondary" type="submit" style="background: transparent; color: var(--accent); border-color: var(--border);">Delete</button>
                        </form>
                    </div>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php include __DIR__ . '/templates/footer.php'; ?>