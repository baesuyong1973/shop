import { Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

const LINKS = [
    { slug: 'how-to-use', key: 'howToUse' },
    { slug: 'privacy', key: 'privacy' },
    { slug: 'contact', key: 'contact' },
    { slug: 'company', key: 'company' },
];

export default function Footer() {
    const { t } = useTranslation();

    return (
        <footer className="border-t border-gray-200 bg-white">
            <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <nav className="flex flex-wrap justify-center gap-x-6 gap-y-2">
                    {LINKS.map(({ slug, key }) => (
                        <Link
                            key={slug}
                            href={route('pages.show', slug)}
                            className="text-sm text-gray-500 hover:text-gray-700"
                        >
                            {t(`staticPages.titles.${key}`)}
                        </Link>
                    ))}
                </nav>
            </div>
        </footer>
    );
}
