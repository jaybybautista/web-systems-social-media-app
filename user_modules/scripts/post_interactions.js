// Post Interactions - Likes, Comments, and Reports
document.addEventListener("DOMContentLoaded", function () {
  // Comment button toggle
  document.querySelectorAll(".comment-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      const postId = this.dataset.postId;
      const commentSection = document.getElementById(`comments-${postId}`);

      if (commentSection.style.display === "none") {
        commentSection.style.display = "block";
        this.classList.add("active");
        loadComments(postId);
      } else {
        commentSection.style.display = "none";
        this.classList.remove("active");
      }
    });
  });

  // Like button functionality
document.querySelectorAll(".like-btn").forEach((btn) => {
  btn.addEventListener("click", function () {
    const postId = this.dataset.postId;

    // Use x-www-form-urlencoded
    fetch("../user_modules/like_post.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `post_id=${encodeURIComponent(postId)}`
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        this.classList.toggle("active"); // toggle button style
        const countSpan = this.querySelector(".action-count");
        countSpan.textContent = data.like_count; // update count
      } else {
        alert(data.message || "Error liking post");
      }
    })
    .catch(error => console.error("Error:", error));
  });
});


//----------------------------------------------------------------------------------
//comment
// 
document.querySelectorAll(".comment-submit-btn").forEach((btn) => {
  btn.addEventListener("click", function () {
    const postId = this.dataset.postId;
    const commentInput = document.querySelector(`.comment-input[data-post-id="${postId}"]`);
    const commentText = commentInput.value.trim();

    if (commentText === "") {
      alert("Please write a comment");
      return;
    }

    const formData = new URLSearchParams();
    formData.append('post_id', postId);
    formData.append('comment_text', commentText);
    formData.append('parent_id', ''); // empty string = NULL

    fetch("../user_modules/add_comment.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: formData.toString()
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        commentInput.value = "";
        loadComments(postId);

        // Update comment count
        const commentBtn = document.querySelector(`.comment-btn[data-post-id="${postId}"]`);
        const countSpan = commentBtn.querySelector(".action-count");
        countSpan.textContent = data.comment_count;
      } else {
        alert(data.message || "Failed to post comment");
      }
    })
    .catch(error => console.error("Error:", error));
  });
});

// Load comments
function loadComments(postId) {
  fetch(`../user_modules/get_comments.php?post_id=${postId}`)
    .then(res => res.json())
    .then(data => {
      const commentsList = document.querySelector(`#comments-${postId} .comments-list`);

      if (!data.comments || data.comments.length === 0) {
        commentsList.innerHTML = '<div class="empty-comments">No comments yet. Be the first to comment!</div>';
        return;
      }

      // Build HTML for each comment
      let html = '';
      data.comments.forEach(comment => {
        html += `
          <div class="comment-item">
            <img src="../uploads/${comment.user_picture}" alt="Profile" class="comment-pic">
            <div class="comment-content">
              <div class="comment-author">${comment.username}</div>
              <div class="comment-text">${escapeHtml(comment.content)}</div>
              <div class="comment-time">${formatTime(comment.created_at)}</div>
            </div>
          </div>
        `;
      });

      commentsList.innerHTML = html;
    })
    .catch(err => console.error("Error loading comments:", err));
}

// Format time helper
function formatTime(timestamp) {
  const date = new Date(timestamp);
  const now = new Date();
  const diff = now - date;

  const minutes = Math.floor(diff / 60000);
  const hours = Math.floor(diff / 3600000);
  const days = Math.floor(diff / 86400000);

  if (minutes < 1) return "Just now";
  if (minutes < 60) return `${minutes}m ago`;
  if (hours < 24) return `${hours}h ago`;
  if (days < 7) return `${days}d ago`;

  return date.toLocaleDateString("en-US", { month: "short", day: "numeric" });
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}




//----------------------------------------------------------------------------------

  // Report button: open modal and set post id
  document.querySelectorAll(".post-report-btn").forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      const postCard = this.closest(".post-card");
      const postId = postCard.querySelector(".like-btn").dataset.postId;

      window.currentReportPostId = postId;
      document.getElementById("reportReason").value = "";
      document.getElementById("reportModal").classList.add("show");
    });
  });

  // Report modal buttons
  document
    .getElementById("reportCancel")
    .addEventListener("click", function () {
      document.getElementById("reportModal").classList.remove("show");
    });

  // Report form submit: set hidden post_id and let form submit normally
  const reportForm = document.getElementById("reportForm");
  if (reportForm) {
    reportForm.addEventListener("submit", function (e) {
      const postId = window.currentReportPostId;
      if (!postId) {
        e.preventDefault();
        alert("Post ID missing. Please try again.");
        return;
      }
      document.getElementById("reportPostId").value = postId;
      // allow normal form POST
    });
  }

  // Close modal when clicking outside
  document
    .getElementById("reportModal")
    .addEventListener("click", function (e) {
      if (e.target === this) {
        this.classList.remove("show");
      }
    });
});

// Load and display comments
function loadComments(postId) {
  fetch("../user_modules/get_comments.php?post_id=" + postId)
    .then((response) => response.json())
    .then((data) => {
      const commentsList = document.querySelector(
        `#comments-${postId} .comments-list`
      );

      if (data.comments && data.comments.length > 0) {
        commentsList.innerHTML = data.comments
          .map(
            (comment) => `
          <div class="comment-item">
            <img src="../uploads/${
              comment.user_picture
            }" alt="Profile" class="comment-pic">
            <div class="comment-content">
              <div class="comment-author">${escapeHtml(comment.username)}</div>
              <div class="comment-text">${escapeHtml(
                comment.comment_text
              )}</div>
              <div class="comment-time">${formatTime(comment.created_at)}</div>
            </div>
          </div>
        `
          )
          .join("");
      } else {
        commentsList.innerHTML =
          '<div class="empty-comments">No comments yet. Be the first to comment!</div>';
      }
    })
    .catch((error) => {
      console.error("Error loading comments:", error);
    });
}

// Helper function to escape HTML
function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

// Helper function to format time
function formatTime(timestamp) {
  const date = new Date(timestamp);
  const now = new Date();
  const diff = now - date;

  const minutes = Math.floor(diff / 60000);
  const hours = Math.floor(diff / 3600000);
  const days = Math.floor(diff / 86400000);

  if (minutes < 1) return "Just now";
  if (minutes < 60) return `${minutes}m ago`;
  if (hours < 24) return `${hours}h ago`;
  if (days < 7) return `${days}d ago`;

  return date.toLocaleDateString("en-US", { month: "short", day: "numeric" });
}
