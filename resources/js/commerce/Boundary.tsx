import { Component, type ReactNode } from 'react';

export default class Boundary extends Component<{ children: ReactNode }, { failed: boolean }> {
    state = { failed: false };
    static getDerivedStateFromError() { return { failed: true }; }
    render() {
        if (this.state.failed) return <main role="alert" style={{ padding: 32 }}><h1>Không thể hiển thị giao diện trợ lý</h1><p>Vui lòng tải lại trang để thử lại.</p><button onClick={() => location.reload()}>Tải lại</button><a href="/"> Về Travel Planner</a></main>;
        return this.props.children;
    }
}
