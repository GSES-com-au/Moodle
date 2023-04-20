function updateexpiration(courseFullname) {
  // Find the course in the $usercourseprofile array with a matching id
  var course = $usercourseprofile.find(function (c) {
    return c.fullname === courseFullname;
  });

  // Format the expiration date as a string in the dd/mm/yyyy format
  var expiration = date("dd/mm/yyyy", course.timeend);

  // Set the value of the expiration input field to the formatted expiration date
  document.getElementById("expiration").value = expiration;

  // Add an onchange event listener to the select element
  document
    .getElementById("course")
    .addEventListener("change", function (event) {
      // Call the updateExpiration function when the course is changed
      updateexpiration(event.target.value);
    });
}
