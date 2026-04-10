export class DeviceManager {
    constructor(controllerContext) {
        this.ctx = controllerContext; // le controller Stimulus
    }

    init() {
        if (!['desktop', 'mobile'].includes(this.ctx.deviceValue)) {
            this.ctx.deviceValue = 'desktop';
        }
        this.updateButtons();
    }

    get current() {
        return this.ctx.deviceValue || 'desktop';
    }

    switchTo(device) {
        this.ctx.deviceValue = device === 'mobile' ? 'mobile' : 'desktop';
        this.updateButtons();
    }

    updateButtons() {
        const currentDevice = this.current;

        if (!this.ctx.hasDeviceButtonTarget) {
            return;
        }

        this.ctx.deviceButtonTargets.forEach((btn) => {
            const isActive = btn.dataset.device === currentDevice;
            btn.classList.toggle('pb-device-switch-btn--active', isActive);
        });
    }
}
