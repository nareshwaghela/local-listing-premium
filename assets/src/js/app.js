import Alpine from 'alpinejs';

// Auto-register all components
const componentModules = import.meta.glob('./components/*.js');

async function registerComponents() {
    for (const [path, module] of Object.entries(componentModules)) {
        const name = path.match(/\/([^/]+)\.js$/)[1];
        const camelName = name.replace(/-([a-z])/g, (_, c) => c.toUpperCase());
        const component = await module();
        Alpine.data(camelName, component.default || component);
    }
}

registerComponents().then(() => {
    window.Alpine = Alpine;
    Alpine.start();
});
