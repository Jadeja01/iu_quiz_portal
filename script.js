document.addEventListener("DOMContentLoaded", () => {
    // LOGIN FUNCTION
    document.getElementById("loginForm").addEventListener("submit", async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData);

        try {
            const res = await fetch("login.php", {
                method: "POST",
                body: JSON.stringify(data),
                headers: { "Content-Type": "application/json" }
            });
            const result = await res.json();

            if (result.success) {
                alert("Login successful!");
                if (result.role === "admin") window.location.href = "/admin.php";
                else window.location.href = "/user.php";
            } else {
                alert(result.message);
            }
        } catch (err) {
            console.error(err);
            alert("Something went wrong!");
        }
    });

    // SIGNUP FUNCTION
    document.getElementById("signupForm").addEventListener("submit", async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData);

        try {
            const res = await fetch("signup.php", {
                method: "POST",
                body: JSON.stringify(data),
                headers: { "Content-Type": "application/json" }
            });
            const result = await res.json();

            if (result.success) {
                alert("Signup successful! Redirecting...");
                if (result.role === "admin") window.location.href = "/admin.php";
                else window.location.href = "/user.php";
            } else {
                alert(result.message);
            }
        } catch (err) {
            console.error(err);
            alert("Something went wrong!");
        }
    });
});
