(() => {
    'use strict'
    const forms = document.querySelectorAll('.needs-validation')
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault()
            
                event.stopPropagation()
                Toast.fire({
                icon: 'error',
                title: 'Something With Wrong !'
            })
            }
            else{
                Toast.fire({
                icon: 'success',
                title: 'Submit Successfully'
            })
            }
            form.classList.add('was-validated')

            
        }, false)
    })
})();
function alertShow(){
    Toast.fire({
        icon: 'error',
        title: 'Something With Wrong !'
    })
}

document.getElementById('signUpBtn').addEventListener('click', (e) => {
    e.preventDefault()
    Toast.fire({
        icon: "success",
        title: "Signin Successfully",
    });
})
document.getElementById('signinBtn').addEventListener('click', (e) => {
    e.preventDefault()
    Toast.fire({
        icon: "success",
        title: "Signin Successfully",
    });
})
document.getElementById('loginBtn').addEventListener('click', (e) => {
    e.preventDefault()
    Toast.fire({
        icon: "success",
        title: "Login Successfully",
    });
})
