export default {
    name: "WireLink",
    functional: true,
    props: {
        href: {
            type: String,
            required: true,
        },
        method: {
            type: String,
            default: 'get',
        },
        replace: {
            type: Boolean,
            default: false,
        },
        preserveScroll: {
            type: Boolean,
            default: false,
        },
        as: {
            type: String,
            default: "a",
        }
    },
    render(h, node) {
        let props = node.props, data = node.data, children = node.children;
        const visit = event => {
            event.preventDefault()
            node.parent.$store.state.ternoboWireApp.visit(props.href, {
                method: props.method
            });
            console.log
        }

        return h(props.as, {
            ...data,
            attrs: {
                ...data.attrs,
                href: props.href,
            },
            on: {
                ...(data.on || {}),
                click: visit,
            },
        }, children)
    },
}
