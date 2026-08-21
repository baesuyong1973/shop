import Footer from '@/Components/Footer';
import { Head, Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

const TITLE_KEYS = {
    'how-to-use': 'howToUse',
    privacy: 'privacy',
    contact: 'contact',
    company: 'company',
};

export default function Show({ slug }) {
    const { t } = useTranslation();
    const title = t(`staticPages.titles.${TITLE_KEYS[slug]}`);

    return (
        <div className="flex min-h-screen flex-col bg-gray-100">
            <Head title={title} />

            <nav className="border-b border-gray-100 bg-white">
                <div className="mx-auto flex h-16 max-w-7xl items-center px-4 sm:px-6 lg:px-8">
                    <Link
                        href={route('shops.index')}
                        className="text-sm text-gray-600 underline hover:text-gray-900"
                    >
                        {t('staticPages.backToHome')}
                    </Link>
                </div>
            </nav>

            <div className="flex-1 py-12">
                <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                        <h1 className="mb-4 text-xl font-bold text-gray-900">
                            {title}
                        </h1>
                        <p className="whitespace-pre-wrap text-sm leading-relaxed text-gray-700">
                            {t('staticPages.placeholder')}
                        </p>
                    </div>
                </div>
            </div>

            <Footer />
        </div>
    );
}
