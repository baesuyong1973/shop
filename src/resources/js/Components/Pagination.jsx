import { Link } from '@inertiajs/react';

export default function Pagination({ links }) {
    if (links.length <= 3) {
        return null;
    }

    return (
        <nav className="mt-4 flex flex-wrap items-center justify-center gap-1">
            {links.map((link, index) => {
                if (link.url === null) {
                    return (
                        <span
                            key={index}
                            className="rounded-md px-3 py-1 text-sm text-gray-400"
                            dangerouslySetInnerHTML={{ __html: link.label }}
                        />
                    );
                }

                return (
                    <Link
                        key={index}
                        href={link.url}
                        preserveScroll
                        className={
                            'rounded-md px-3 py-1 text-sm ' +
                            (link.active
                                ? 'bg-gray-800 text-white'
                                : 'text-gray-600 hover:bg-gray-100')
                        }
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                );
            })}
        </nav>
    );
}
