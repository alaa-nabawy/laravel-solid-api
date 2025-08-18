import { Link, router } from "@inertiajs/react";

export default function DropdownLink({
    href,
    method = "get",
    as = "link",
    children,
    ...props
}) {
    const handleClick = () => {
        if (href && method !== "get") {
            router.visit(href, {
                method: method,
            });
        }
    };

    if (as === "link") {
        return (
            <Link
                href={href}
                method={method}
                as={as}
                className="block w-full px-4 py-2 text-left text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out"
                {...props}
            >
                {children}
            </Link>
        );
    }

    return (
        <button
            type={as}
            className="block w-full px-4 py-2 text-left text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out"
            onClick={handleClick}
            {...props}
        >
            {children}
        </button>
    );
}
