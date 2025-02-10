document.addEventListener("DOMContentLoaded", () => {
  const studentBtn = document.getElementById("manage-student-btn");
  const courseBtn = document.getElementById("manage-course-btn");
  const studentPanel = document.getElementById("manage-student");
  const coursePanel = document.getElementById("manage-course");

  studentBtn.addEventListener("click", () => {
      studentPanel.classList.remove("d-none");
      coursePanel.classList.add("d-none");
      studentBtn.classList.add("btn-dark");
      studentBtn.classList.remove("btn-light");
      courseBtn.classList.add("btn-light");
      courseBtn.classList.remove("btn-dark");
  });

  courseBtn.addEventListener("click", () => {
      coursePanel.classList.remove("d-none");
      studentPanel.classList.add("d-none");
      courseBtn.classList.add("btn-dark");
      courseBtn.classList.remove("btn-light");
      studentBtn.classList.add("btn-light");
      studentBtn.classList.remove("btn-dark");
  });
});