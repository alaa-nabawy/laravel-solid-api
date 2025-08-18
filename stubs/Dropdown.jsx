import { useState, useEffect, useRef } from "react";
import { clsx } from "clsx";

export default function Dropdown({
    align = "right",
    width = "48",
    contentClasses = "py-1 bg-white",
    children,
}) {
    const [open, setOpen] = useState(false);
    const dropdownRef = useRef(null);

    useEffect(() => {
        const closeOnEscape = (e) => {
            if (open && e.key === "Escape") {
                setOpen(false);
            }
        };

        document.addEventListener("keydown", closeOnEscape);
        return () => document.removeEventListener("keydown", closeOnEscape);
    }, [open]);

    const widthClass = {
        48: "w-48",
    }[width.toString()];

    const alignmentClasses =
        {
            left: "origin-top-left left-0",
            right: "origin-top-right right-0",
        }[align] || "origin-top";

    return (
        <div className="relative" ref={dropdownRef}>
            <div onClick={() => setOpen(!open)}>{children.trigger}</div>

            {/* Full Screen Dropdown Overlay */}
            {open && (
                <div
                    className="fixed inset-0 z-40"
                    onClick={() => setOpen(false)}
                />
            )}

            {/* Dropdown Content */}
            <div
                className={clsx(
                    "absolute z-50 mt-2 rounded-md shadow-lg transition-all duration-200",
                    widthClass,
                    alignmentClasses,
                    open
                        ? "transform opacity-100 scale-100"
                        : "transform opacity-0 scale-95 pointer-events-none",
                )}
                onClick={() => setOpen(false)}
            >
                <div
                    className={clsx(
                        "rounded-md ring-1 ring-black ring-opacity-5",
                        contentClasses,
                    )}
                >
                    {children.content}
                </div>
            </div>
        </div>
    );
}

// Compound components
Dropdown.Trigger = function DropdownTrigger({ children }) {
    return children;
};

Dropdown.Content = function DropdownContent({ children }) {
    return children;
};
