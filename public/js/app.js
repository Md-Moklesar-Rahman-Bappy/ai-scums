/* =====================================================================
   AI-SCUMS — Frontend interactivity
   Vanilla JS (no framework). Depends on Bootstrap 5.3, axios, Chart.js
   where present. Degrades gracefully if a dependency is missing.
   ===================================================================== */
(function () {
    "use strict";

    const html = document.documentElement;
    const body = document.body;
    const STORAGE_THEME = "scums-theme";
    const STORAGE_SIDEBAR = "scums-sidebar-collapsed";

    /* ---------- Theme (dark mode) ---------- */
    function applyTheme(theme) {
        html.setAttribute("data-bs-theme", theme);
        const btn = document.getElementById("themeToggle");
        if (btn) {
            btn.innerHTML = theme === "dark"
                ? '<i class="bi bi-sun"></i>'
                : '<i class="bi bi-moon-stars"></i>';
            btn.setAttribute("aria-label", theme === "dark" ? "Switch to light mode" : "Switch to dark mode");
            btn.setAttribute("title", theme === "dark" ? "Light mode" : "Dark mode");
        }
    }

    function initTheme() {
        let theme = localStorage.getItem(STORAGE_THEME);
        if (!theme) {
            theme = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
        }
        applyTheme(theme);
    }

    document.addEventListener("click", function (e) {
        const t = e.target.closest("#themeToggle");
        if (t) {
            const next = html.getAttribute("data-bs-theme") === "dark" ? "light" : "dark";
            localStorage.setItem(STORAGE_THEME, next);
            applyTheme(next);
            if (window.ScumsCharts) window.ScumsCharts.refresh();
        }
    });

    /* ---------- Sidebar collapse (desktop) ---------- */
    function initSidebar() {
        if (localStorage.getItem(STORAGE_SIDEBAR) === "1") body.classList.add("sidebar-collapsed");
        document.addEventListener("click", function (e) {
            if (e.target.closest("#sidebarToggle")) {
                body.classList.toggle("sidebar-collapsed");
                localStorage.setItem(STORAGE_SIDEBAR, body.classList.contains("sidebar-collapsed") ? "1" : "0");
            }
        });
    }

    /* ---------- Mobile sidebar drawer ---------- */
    function openMobile() {
        const sb = document.getElementById("appSidebar");
        const bd = document.getElementById("sidebarBackdrop");
        if (sb) sb.classList.add("mobile-open");
        if (bd) bd.classList.add("open");
    }
    function closeMobile() {
        const sb = document.getElementById("appSidebar");
        const bd = document.getElementById("sidebarBackdrop");
        if (sb) sb.classList.remove("mobile-open");
        if (bd) bd.classList.remove("open");
    }
    document.addEventListener("click", function (e) {
        if (e.target.closest("#mobileMenuBtn")) openMobile();
        if (e.target.closest("#sidebarBackdrop")) closeMobile();
    });

    /* ---------- Command palette (Ctrl/Cmd + K) ---------- */
    const cmdkRoutes = (window.SCUMS_COMMANDS || []);
    function renderCmdkResults(q) {
        const box = document.getElementById("cmdkResults");
        if (!box) return;
        const query = (q || "").toLowerCase();
        const items = cmdkRoutes.filter(r => !query || r.label.toLowerCase().includes(query) || (r.keywords || "").includes(query)).slice(0, 12);
        box.innerHTML = items.length
            ? items.map((r, i) =>
                `<a class="cmdk-item ${i === 0 ? "active" : ""}" href="${r.url}" data-i="${i}">
                    <i class="${r.icon}"></i><span>${r.label}</span>
                </a>`).join("")
            : `<div class="p-3 text-muted small">No results found.</div>`;
        box.querySelectorAll(".cmdk-item").forEach(el => {
            el.addEventListener("click", closeCmdk);
        });
    }
    function openCmdk() {
        const b = document.getElementById("cmdkBackdrop");
        const c = document.getElementById("cmdk");
        if (!b || !c) return;
        b.classList.add("open"); c.classList.add("open");
        const input = document.getElementById("cmdkInput");
        if (input) { input.value = ""; renderCmdkResults(""); input.focus(); }
    }
    function closeCmdk() {
        const b = document.getElementById("cmdkBackdrop");
        const c = document.getElementById("cmdk");
        if (b) b.classList.remove("open");
        if (c) c.classList.remove("open");
    }
    document.addEventListener("keydown", function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === "k") { e.preventDefault(); openCmdk(); }
        if (e.key === "Escape") { closeCmdk(); closeMobile(); }
    });
    document.addEventListener("click", function (e) {
        if (e.target.closest("#cmdkBackdrop")) closeCmdk();
        if (e.target.closest("#searchTrigger")) openCmdk();
    });
    document.addEventListener("input", function (e) {
        if (e.target && e.target.id === "cmdkInput") renderCmdkResults(e.target.value);
    });

    /* ---------- Tenant switcher (super admin) ---------- */
    function initTenantSwitch() {
        const sel = document.getElementById("tenantSwitch");
        if (!sel) return;
        sel.addEventListener("change", function () {
            const payload = { institution_id: this.value || "" };
            const done = () => location.reload();
            if (window.axios) {
                window.axios.post(window.SCUMS_TENANT_SWITCH_URL || "/tenant/switch", payload)
                    .then(done).catch(done);
            } else {
                const f = document.createElement("form");
                f.method = "POST"; f.action = window.SCUMS_TENANT_SWITCH_URL || "/tenant/switch";
                f.innerHTML = '@csrf'.replace('@csrf', '<input type="hidden" name="_token" value="' + (document.querySelector('meta[name="csrf-token"]') || {}).content + '">') + '<input type="hidden" name="institution_id" value="' + this.value + '">';
                document.body.appendChild(f); f.submit();
            }
        });
    }

    /* ---------- Confirm delete (links/buttons with data-confirm) ---------- */
    document.addEventListener("click", function (e) {
        const el = e.target.closest("[data-confirm]");
        if (el && !window.confirm(el.getAttribute("data-confirm"))) e.preventDefault();
    });

    /* ---------- Auto-dismiss alerts ---------- */
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".alert[data-auto-close]").forEach(a => {
            setTimeout(() => { const c = bootstrap && bootstrap.Alert ? bootstrap.Alert.getOrCreateInstance(a) : null; if (c) c.close(); }, 4000);
        });
    });

    /* ---------- Init ---------- */
    initTheme();
    initSidebar();
    initTenantSwitch();
})();
