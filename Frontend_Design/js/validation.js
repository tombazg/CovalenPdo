// document.addEventListener('DOMContentLoaded', function() {
//     document.querySelectorAll('form').forEach(function(form) {
//         form.addEventListener('submit', function(event) {
//             let isValid = true;
//             const inputs = form.querySelectorAll('input[required], textarea[required]');
            
//             inputs.forEach(function(input) {
//                 if (input.value.trim() === '') { // Trim whitespace
//                     isValid = false;
//                     input.classList.add('is-invalid');
//                 } else {
//                     input.classList.remove('is-invalid');
//                 }
//             });
            
//             if (!isValid) {
//                 alert('Please fill out all required fields.');
//                 event.preventDefault();
//             }
//         });
//     });
// });

// function validateEmail(email) {
//     const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // Corrected regex
//     return re.test(String(email).toLowerCase());
// }
