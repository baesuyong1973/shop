import Footer from '@/Components/Footer';
import { Head, Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

const TITLE_KEYS = {
    'how-to-use': 'howToUse',
    privacy: 'privacy',
    company: 'company',
};

const COMPANY_FIELDS = [
    'name',
    'representative',
    'established',
    'capital',
    'address',
    'business',
];

const PRIVACY_LIST_SECTIONS = ['collect', 'purpose'];
const PRIVACY_TEXT_SECTIONS = ['thirdParty', 'contact'];

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

                        {slug === 'company' && (
                            <dl className="mb-6 divide-y divide-gray-200 border-t border-gray-200">
                                {COMPANY_FIELDS.map((field) => (
                                    <div
                                        key={field}
                                        className="grid grid-cols-3 gap-4 py-3 sm:grid-cols-4"
                                    >
                                        <dt className="text-sm font-medium text-gray-500">
                                            {t(
                                                `staticPages.company.fields.${field}`,
                                            )}
                                        </dt>
                                        <dd className="col-span-2 text-sm text-gray-900 sm:col-span-3">
                                            {t(
                                                `staticPages.company.values.${field}`,
                                            )}
                                        </dd>
                                    </div>
                                ))}
                            </dl>
                        )}

                        {slug === 'how-to-use' && (
                            <ul className="list-disc space-y-2 pl-5 text-sm leading-relaxed text-gray-700">
                                {t('staticPages.howToUsePage.steps', {
                                    returnObjects: true,
                                }).map((step) => (
                                    <li key={step}>{step}</li>
                                ))}
                            </ul>
                        )}

                        {slug === 'privacy' && (
                            <div className="space-y-6">
                                <p className="text-sm leading-relaxed text-gray-700">
                                    {t('staticPages.privacyPage.intro')}
                                </p>

                                {PRIVACY_LIST_SECTIONS.map((key) => (
                                    <div key={key}>
                                        <h2 className="mb-2 text-base font-semibold text-gray-900">
                                            {t(
                                                `staticPages.privacyPage.sections.${key}.title`,
                                            )}
                                        </h2>
                                        <ul className="list-disc space-y-1 pl-5 text-sm text-gray-700">
                                            {t(
                                                `staticPages.privacyPage.sections.${key}.items`,
                                                { returnObjects: true },
                                            ).map((item) => (
                                                <li key={item}>{item}</li>
                                            ))}
                                        </ul>
                                    </div>
                                ))}

                                {PRIVACY_TEXT_SECTIONS.map((key) => (
                                    <div key={key}>
                                        <h2 className="mb-2 text-base font-semibold text-gray-900">
                                            {t(
                                                `staticPages.privacyPage.sections.${key}.title`,
                                            )}
                                        </h2>
                                        <p className="text-sm leading-relaxed text-gray-700">
                                            {t(
                                                `staticPages.privacyPage.sections.${key}.body`,
                                            )}
                                        </p>
                                    </div>
                                ))}
                            </div>
                        )}

                        {slug !== 'company' &&
                            slug !== 'privacy' &&
                            slug !== 'how-to-use' && (
                                <p className="whitespace-pre-wrap text-sm leading-relaxed text-gray-700">
                                    {t('staticPages.placeholder')}
                                </p>
                            )}
                    </div>
                </div>
            </div>

            <Footer />
        </div>
    );
}
