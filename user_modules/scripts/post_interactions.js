document.addEventListener("DOMContentLoaded", function () {

  /* =========================
     COMMENT TOGGLE
  ========================== */
  document.querySelectorAll(".comment-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      const postId = this.dataset.postId;
      const commentSection = document.getElementById(`comments-${postId}`);

      if (!commentSection) return;

      const isHidden = commentSection.style.display === "none" || commentSection.style.display === "";
      commentSection.style.display = isHidden ? "block" : "none";
      this.classList.toggle("active", isHidden);

      if (isHidden) loadComments(postId);
    });
  });

  /* =========================
     LIKE BUTTON
  ========================== */
  document.querySelectorAll(".like-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      const postId = this.dataset.postId;

      fetch("../user_modules/like_post.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `post_id=${encodeURIComponent(postId)}`
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            this.classList.toggle("active");
            this.querySelector(".action-count").textContent = data.like_count;
          } else {
            alert(data.message || "Error liking post");
          }
        });
    });
  });

  /* =========================
     COMMENT SUBMIT
  ========================== */
  document.querySelectorAll(".comment-submit-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      const postId = this.dataset.postId;
      const input = document.querySelector(`.comment-input[data-post-id="${postId}"]`);
      if (!input || input.value.trim() === "") {
        alert("Please write a comment");
        return;
      }

      fetch("../user_modules/add_comment.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `post_id=${postId}&comment_text=${encodeURIComponent(input.value)}`
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            input.value = "";
            loadComments(postId);

            const countSpan = document.querySelector(`.comment-btn[data-post-id="${postId}"] .action-count`);
            if (countSpan) countSpan.textContent = data.comment_count;
          }
        });
    });
  });

  /* =========================
     REPORT POST
  ========================== */
  document.querySelectorAll(".post-report-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      const postCard = this.closest(".post-card");
      const postId = postCard.querySelector(".like-btn").dataset.postId;

      window.currentReportPostId = postId;
      document.getElementById("reportReason").value = "";
      document.getElementById("reportModal").classList.add("show");
    });
  });

  document.getElementById("reportCancel")?.addEventListener("click", () => {
    document.getElementById("reportModal").classList.remove("show");
    window.currentReportPostId = null;
  });

  document.getElementById("reportForm")?.addEventListener("submit", function (e) {
    if (!window.currentReportPostId) {
      e.preventDefault();
      return;
    }
    document.getElementById("reportPostId").value = window.currentReportPostId;
  });

  document.getElementById("reportModal")?.addEventListener("click", function (e) {
    if (e.target === this) this.classList.remove("show");
  });

});

/* =========================
   LOAD COMMENTS
========================== */
function loadComments(postId) {
  fetch(`../user_modules/get_comments.php?post_id=${postId}`)
    .then(res => res.json())
    .then(data => {
      const container = document.querySelector(`#comments-${postId} .comments-list`);
      if (!container) return;

      if (!data.comments || data.comments.length === 0) {
        container.innerHTML = `<div class="empty-comments">No comments yet.</div>`;
        return;
      }

      container.innerHTML = data.comments.map(comment => `
        <div class="comment-item">
          <img src="../uploads/${comment.user_picture}" class="comment-pic">
          <div class="comment-content">
            <div class="comment-author">${escapeHtml(comment.username)}</div>
            <div class="comment-text">${escapeHtml(comment.comment_text)}</div>
            <div class="comment-time">${formatTime(comment.created_at)}</div>
          </div>
        </div>
      `).join("");
    });
}

function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

function formatTime(timestamp) {
  const diff = new Date() - new Date(timestamp);
  const min = Math.floor(diff / 60000);
  if (min < 1) return "Just now";
  if (min < 60) return `${min}m ago`;
  const hrs = Math.floor(min / 60);
  if (hrs < 24) return `${hrs}h ago`;
  return new Date(timestamp).toLocaleDateString();
}
