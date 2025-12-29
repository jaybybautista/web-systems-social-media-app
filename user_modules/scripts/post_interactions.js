document.addEventListener("DOMContentLoaded", function () {

  /* ---------------- COMMENTS TOGGLE ---------------- */
  document.querySelectorAll(".comment-btn").forEach(btn => {
    btn.addEventListener("click", function () {
      const postId = this.dataset.postId;
      const section = document.getElementById(`comments-${postId}`);

      if (section.style.display === "none") {
        section.style.display = "block";
        loadComments(postId);
      } else {
        section.style.display = "none";
      }
    });
  });

  /* ---------------- LIKE ---------------- */
  document.querySelectorAll(".like-btn").forEach(btn => {
    btn.addEventListener("click", function () {
      const postId = this.dataset.postId;

      fetch("../user_modules/like_post.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `post_id=${postId}`
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            this.querySelector(".action-count").textContent = data.like_count;
          }
        });
    });
  });

  /* ---------------- ADD COMMENT ---------------- */
  document.querySelectorAll(".comment-submit-btn").forEach(btn => {
    btn.addEventListener("click", function () {
      const postId = this.dataset.postId;
      const input = document.querySelector(`.comment-input[data-post-id="${postId}"]`);
      const text = input.value.trim();

      if (!text) return alert("Please write a comment");

      fetch("../user_modules/add_comment.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `post_id=${postId}&comment_text=${encodeURIComponent(text)}`
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            input.value = "";
            loadComments(postId);
          }
        });
    });
  });

  /* ---------------- REPORT MODAL ---------------- */
  document.querySelectorAll(".post-report-btn").forEach(btn => {
    btn.addEventListener("click", function (e) {
      e.preventDefault();

      const postId = this.closest(".post-card")
        .querySelector(".like-btn").dataset.postId;

      document.getElementById("reportPostId").value = postId;
      document.getElementById("reportReason").value = "";
      document.getElementById("reportModal").classList.add("show");
    });
  });

  document.getElementById("reportCancel").addEventListener("click", () => {
    document.getElementById("reportModal").classList.remove("show");
  });

  document.getElementById("reportModal").addEventListener("click", e => {
    if (e.target.id === "reportModal") {
      e.target.classList.remove("show");
    }
  });

});

/* ---------------- LOAD COMMENTS ---------------- */
function loadComments(postId) {
  fetch(`../user_modules/get_comments.php?post_id=${postId}`)
    .then(res => res.json())
    .then(data => {
      const list = document.querySelector(`#comments-${postId} .comments-list`);
      if (!data.comments || data.comments.length === 0) {
        list.innerHTML = "<p>No comments yet.</p>";
        return;
      }

      list.innerHTML = data.comments.map(c => `
        <div class="comment-item">
          <img src="../uploads/${c.user_picture}" class="comment-pic">
          <div>
            <strong>${escapeHtml(c.username)}</strong>
            <p>${escapeHtml(c.comment_text)}</p>
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
