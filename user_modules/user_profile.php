<style>
:root {
    --glass-bg: rgba(255, 255, 255, 0.65);
    --glass-border: rgba(255, 255, 255, 0.35);
    --glass-shadow: 0 20px 40px rgba(0,0,0,0.08);
    --accent: #4db6ff;
    --accent-dark: #2a8bdc;
    --text-main: #0f172a;
    --text-sub: #475569;
}

/* RESET */
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", system-ui, sans-serif;
}

body {
    min-height: 100vh;
    display: flex;
    background:
        linear-gradient(120deg, #dff4ff, #eefcff, #dbeafe);
    color: var(--text-main);
}

/* LIQUID GLASS EFFECT */
.glass {
    background: var(--glass-bg);
    backdrop-filter: blur(18px) saturate(160%);
    -webkit-backdrop-filter: blur(18px) saturate(160%);
    border: 1px solid var(--glass-border);
    box-shadow: var(--glass-shadow);
}

/* SIDEBARS */
.sidebar-left,
.sidebar-right {
    width: 240px;
    padding: 24px;
}

.sidebar-left {
    border-right: 1px solid var(--glass-border);
}

.sidebar-right {
    border-left: 1px solid var(--glass-border);
}

.sidebar-left h3 {
    font-size: 22px;
    font-weight: 600;
    margin-bottom: 32px;
}

/* NAV */
.nav a {
    display: block;
    padding: 12px 14px;
    margin-bottom: 10px;
    border-radius: 14px;
    text-decoration: none;
    color: var(--text-sub);
    transition: all .25s ease;
}

.nav a:hover {
    background: rgba(77,182,255,0.15);
    color: var(--accent-dark);
}

/* MAIN CONTENT */
.content {
    flex: 1;
    padding: 48px;
}

/* PROFILE CARD */
.profile-card {
    max-width: 920px;
    margin: auto;
    border-radius: 28px;
    overflow: hidden;
}

/* COVER */
.cover {
    height: 220px;
    background-size: cover;
    background-position: center;
    position: relative;
}

/* PROFILE INFO */
.profile-info {
    display: flex;
    gap: 28px;
    padding: 28px 36px 36px;
}

.profile-info img {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    object-fit: cover;
    margin-top: -90px;
    border: 6px solid rgba(255,255,255,0.9);
    background: #fff;
    z-index: 10;
}

/* DETAILS */
.profile-details {
    flex: 1;
}

.profile-details h2 {
    font-size: 28px;
    font-weight: 600;
    margin-bottom: 16px;
}

/* INPUTS */
.profile-details label {
    font-size: 13px;
    color: var(--text-sub);
    margin-top: 14px;
    display: block;
}

.profile-details input,
.profile-details textarea {
    width: 100%;
    padding: 12px 14px;
    border-radius: 14px;
    border: 1px solid rgba(0,0,0,0.08);
    background: rgba(255,255,255,0.75);
    margin-top: 6px;
    font-size: 14px;
    transition: all .2s ease;
}

.profile-details input:focus,
.profile-details textarea:focus {
    outline: none;
    border-color: var(--accent);
    background: #fff;
}

/* BUTTON */
.profile-details button {
    margin-top: 20px;
    padding: 12px 22px;
    border-radius: 16px;
    border: none;
    background: linear-gradient(135deg, var(--accent), var(--accent-dark));
    color: #fff;
    font-size: 15px;
    font-weight: 500;
    cursor: pointer;
    transition: transform .2s ease, box-shadow .2s ease;
}

.profile-details button:hover {
    transform: translateY(-1px);
    box-shadow: 0 10px 20px rgba(77,182,255,0.35);
}

/* FOLLOWERS */
.sidebar-right h4 {
    font-size: 16px;
    margin-bottom: 18px;
}

.follower {
    display: flex;
    align-items: center;
    margin-bottom: 14px;
}

.follower img {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    margin-right: 12px;
}

.follower span {
    font-size: 14px;
    color: var(--text-sub);
}

/* RESPONSIVE */
@media (max-width: 900px) {
    .sidebar-right { display: none; }
}
@media (max-width: 720px) {
    .sidebar-left { display: none; }
    .content { padding: 24px; }
}
</style>
