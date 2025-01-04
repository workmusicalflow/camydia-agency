document.addEventListener("alpine:init", () => {
  Alpine.data("mobileMenu", () => ({
    open: false,
    isOpen: false,

    toggle() {
      this.open = !this.open;
      this.isOpen = !this.isOpen;
    },

    close() {
      this.open = false;
      this.isOpen = false;
    },
  }));
});
