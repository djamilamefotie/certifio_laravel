<style>
    @import url('https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Inter:wght@400;500;600&display=swap');

    :root {
        --certifio-green-950: #081F18;
        --certifio-green-800: #0F3D2E;
        --certifio-gold: #C9A227;
        --certifio-gold-light: #E4C766;
        --certifio-cream: #F4F1E6;
    }

    body.fi-simple-page,
    .fi-simple-layout {
        background: var(--certifio-green-950) !important;
    }

    .fi-simple-layout .fi-logo,
    .fi-simple-layout .fi-simple-header-heading {
        font-family: 'Fraunces', serif;
        color: var(--certifio-cream) !important;
    }

    .fi-simple-layout .fi-simple-main {
        background: linear-gradient(160deg, var(--certifio-green-800), #0B3226) !important;
        border: 1px solid rgba(228,199,102,0.18) !important;
        border-radius: 20px !important;
        box-shadow: 0 40px 80px -30px rgba(0,0,0,0.6) !important;
    }

    .fi-simple-layout label {
        color: rgba(244,241,230,0.6) !important;
    }

    .fi-simple-layout .fi-input,
    .fi-simple-layout input {
        background: rgba(0,0,0,0.18) !important;
        border: 1px solid rgba(228,199,102,0.22) !important;
        color: var(--certifio-cream) !important;
        border-radius: 8px !important;
    }

    .fi-simple-layout .fi-input:focus,
    .fi-simple-layout input:focus {
        border-color: var(--certifio-gold) !important;
        box-shadow: none !important;
    }

    .fi-simple-layout .fi-btn,
    .fi-simple-layout button[type="submit"] {
        background: var(--certifio-gold) !important;
        color: var(--certifio-green-950) !important;
        font-weight: 600 !important;
        border-radius: 8px !important;
    }

    .fi-simple-layout .fi-btn:hover,
    .fi-simple-layout button[type="submit"]:hover {
        background: var(--certifio-gold-light) !important;
    }

    .fi-simple-layout a {
        color: var(--certifio-gold-light) !important;
    }
        .fi-simple-layout {
        min-height: 100vh !important;
    }

    .fi-simple-layout .fi-simple-main-ctn {
        max-width: 24rem !important;
    }

    .fi-simple-layout .fi-simple-main {
        padding: 2rem !important;
    }

    .fi-simple-layout .fi-logo {
        font-size: 1.25rem !important;
    }

    .fi-simple-layout .fi-simple-header-heading {
        font-size: 1.25rem !important;
    }
</style>