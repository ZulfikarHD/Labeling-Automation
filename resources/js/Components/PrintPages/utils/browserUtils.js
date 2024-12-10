export function detectBrowser() {
    // Add your browser detection logic here
    return "Firefox";
}

export function getTopMargin(browser) {
    return browser !== "Firefox" ? "-320px" : "-236px";
}
