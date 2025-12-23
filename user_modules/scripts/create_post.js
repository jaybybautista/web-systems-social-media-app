const createPostBtn = document.querySelector(".create-post-btn");
const uploadForm = document.querySelector(".upload-form");
const submitBtn = document.querySelector(".submit-button");
const closeFormBtn = document.querySelector(".close-form-btn");
const imageUpload = document.querySelector(".image-upload");
const imagePreview = document.querySelector(".image-preview");
const previewImg = document.getElementById("preview-img");
const removeImageBtn = document.querySelector(".remove-image-btn");
const confirmModal = document.getElementById("confirmModal");
const modalCancel = document.getElementById("modalCancel");
const modalConfirm = document.getElementById("modalConfirm");
const postForm = document.querySelector(".upload-form form");
const captionInput = document.querySelector(".caption");

document.querySelector(".logout").addEventListener("click", function () {
  window.location.href = "../logout.php";
});

function openForm() {
  uploadForm.style.display = "flex";
  submitBtn.style.display = "block";
  uploadForm.scrollIntoView({
    behavior: "smooth",
    block: "center",
  });
}

function closeForm() {
  uploadForm.style.display = "none";
  submitBtn.style.display = "none";
  imagePreview.style.display = "none";
  imageUpload.value = "";
}

function removeImage(e) {
  e.preventDefault();
  imagePreview.style.display = "none";
  imageUpload.value = "";
  previewImg.src = "";
}

function showConfirmation(e) {
  e.preventDefault();

  // Check if caption is filled (image is optional)
  const hasCaption = captionInput.value.trim() !== "";

  if (!hasCaption) {
    alert("Please add a caption before posting.");
    return;
  }

  confirmModal.classList.add("show");
}

function hideConfirmation() {
  confirmModal.classList.remove("show");
}

function confirmPost() {
  hideConfirmation();

  const formData = new FormData(postForm);

  // Show loading state
  modalConfirm.disabled = true;
  modalConfirm.textContent = "Posting...";

  fetch(postForm.action, {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Reset form
        captionInput.value = "";
        imageUpload.value = "";
        imagePreview.style.display = "none";
        previewImg.src = "";

        // Close form
        closeForm();

        // Show success message
        alert("Post uploaded successfully!");
      } else {
        alert("Error: " + data.message);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("An error occurred while uploading the post.");
    })
    .finally(() => {
      modalConfirm.disabled = false;
      modalConfirm.textContent = "Post";
    });
}

imageUpload.addEventListener("change", function (event) {
  const file = event.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function (e) {
      previewImg.src = e.target.result;
      imagePreview.style.display = "block";
    };
    reader.readAsDataURL(file);
  }
});

createPostBtn.addEventListener("click", openForm);
closeFormBtn.addEventListener("click", closeForm);
removeImageBtn.addEventListener("click", removeImage);
submitBtn.addEventListener("click", showConfirmation);
modalCancel.addEventListener("click", hideConfirmation);
modalConfirm.addEventListener("click", confirmPost);

// Close modal when clicking outside
confirmModal.addEventListener("click", function (e) {
  if (e.target === confirmModal) {
    hideConfirmation();
  }
});
