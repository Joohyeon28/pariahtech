document.addEventListener("DOMContentLoaded", function () {
  const nameInput = document.getElementById("product_name");
  const priceInput = document.getElementById("product_price");
  const conditionInput = document.getElementById("product_condition");
  const descriptionInput = document.getElementById("product_description");
  const uploadBtn = document.getElementById("upload_button");
  const imageInput = document.getElementById("product_image");

  function checkInputs() {
    if (
      nameInput.value.trim() !== "" &&
      priceInput.value.trim() !== "" &&
      conditionInput.value.trim() !== "" &&
      descriptionInput.value.trim() !== "" &&
      imageInput.files.length > 0
    ) {
      uploadBtn.disabled = false;
      uploadBtn.style.backgroundColor = "#003366"; // dark blue active
      uploadBtn.style.cursor = "pointer";
    } else {
      uploadBtn.disabled = true;
      uploadBtn.style.backgroundColor = "#999"; // greyed out
      uploadBtn.style.cursor = "not-allowed";
    }
  }

  nameInput.addEventListener("input", checkInputs);
  priceInput.addEventListener("input", checkInputs);
  conditionInput.addEventListener("input", checkInputs);
  descriptionInput.addEventListener("input", checkInputs);
  imageInput.addEventListener("change", checkInputs);

  checkInputs();
});
