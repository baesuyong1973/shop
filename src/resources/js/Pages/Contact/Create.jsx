import Footer from '@/Components/Footer';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import { Head, Link, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';

const STEPS = ['input', 'confirm', 'complete'];

const TYPE_OPTIONS = [
    { value: 'bug', labelKey: 'bug' },
    { value: 'shop_registration', labelKey: 'shopRegistration' },
    { value: 'other', labelKey: 'other' },
];

export default function Create({ completed }) {
    const { t } = useTranslation();
    const [step, setStep] = useState('input');
    const { data, setData, post, processing, errors, reset } = useForm({
        type: TYPE_OPTIONS[0].value,
        name: '',
        email: '',
        phone: '',
        message: '',
    });

    useEffect(() => {
        if (completed) {
            setStep('complete');
            reset();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [completed]);

    const toConfirm = (e) => {
        e.preventDefault();
        setStep('confirm');
    };

    const submit = () => {
        post(route('contact.store'));
    };

    const selectedType = TYPE_OPTIONS.find(
        (option) => option.value === data.type,
    );

    return (
        <div className="flex min-h-screen flex-col bg-gray-100">
            <Head title={t('contact.title')} />

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
                <div className="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                        <h1 className="mb-4 text-xl font-bold text-gray-900">
                            {t('contact.title')}
                        </h1>

                        <ol className="mb-6 flex items-center text-sm text-gray-500">
                            {STEPS.map((s, index) => (
                                <li
                                    key={s}
                                    className={`flex items-center ${index < STEPS.length - 1 ? 'flex-1' : ''}`}
                                >
                                    <span
                                        className={
                                            step === s
                                                ? 'font-semibold text-gray-900'
                                                : ''
                                        }
                                    >
                                        {index + 1}. {t(`contact.steps.${s}`)}
                                    </span>
                                    {index < STEPS.length - 1 && (
                                        <span className="mx-2 flex-1 border-t border-gray-300" />
                                    )}
                                </li>
                            ))}
                        </ol>

                        {step === 'input' && (
                            <form onSubmit={toConfirm}>
                                <p className="mb-6 text-sm leading-relaxed text-gray-700">
                                    {t('contact.intro')}
                                </p>

                                <div>
                                    <InputLabel
                                        htmlFor="type"
                                        value={t('contact.fields.type')}
                                    />
                                    <select
                                        id="type"
                                        value={data.type}
                                        onChange={(e) =>
                                            setData('type', e.target.value)
                                        }
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    >
                                        {TYPE_OPTIONS.map((option) => (
                                            <option
                                                key={option.value}
                                                value={option.value}
                                            >
                                                {t(
                                                    `contact.types.${option.labelKey}`,
                                                )}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError
                                        message={errors.type}
                                        className="mt-2"
                                    />
                                </div>

                                <div className="mt-4">
                                    <InputLabel
                                        htmlFor="name"
                                        value={t('contact.fields.name')}
                                    />
                                    <TextInput
                                        id="name"
                                        value={data.name}
                                        className="mt-1 block w-full"
                                        placeholder={t('contact.examples.name')}
                                        onChange={(e) =>
                                            setData('name', e.target.value)
                                        }
                                        required
                                    />
                                    <InputError
                                        message={errors.name}
                                        className="mt-2"
                                    />
                                </div>

                                <div className="mt-4">
                                    <InputLabel
                                        htmlFor="email"
                                        value={t('contact.fields.email')}
                                    />
                                    <TextInput
                                        id="email"
                                        type="email"
                                        value={data.email}
                                        className="mt-1 block w-full"
                                        placeholder={t(
                                            'contact.examples.email',
                                        )}
                                        onChange={(e) =>
                                            setData('email', e.target.value)
                                        }
                                        required
                                    />
                                    <InputError
                                        message={errors.email}
                                        className="mt-2"
                                    />
                                </div>

                                <div className="mt-4">
                                    <InputLabel
                                        htmlFor="phone"
                                        value={t('contact.fields.phone')}
                                    />
                                    <TextInput
                                        id="phone"
                                        type="tel"
                                        value={data.phone}
                                        className="mt-1 block w-full"
                                        placeholder={t(
                                            'contact.examples.phone',
                                        )}
                                        onChange={(e) =>
                                            setData('phone', e.target.value)
                                        }
                                        required
                                    />
                                    <InputError
                                        message={errors.phone}
                                        className="mt-2"
                                    />
                                </div>

                                <div className="mt-4">
                                    <InputLabel
                                        htmlFor="message"
                                        value={t('contact.fields.message')}
                                    />
                                    <textarea
                                        id="message"
                                        value={data.message}
                                        placeholder={t(
                                            'contact.examples.message',
                                        )}
                                        onChange={(e) =>
                                            setData('message', e.target.value)
                                        }
                                        rows={6}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    />
                                    <InputError
                                        message={errors.message}
                                        className="mt-2"
                                    />
                                </div>

                                <div className="mt-6 flex justify-end">
                                    <PrimaryButton type="submit">
                                        {t('contact.toConfirm')}
                                    </PrimaryButton>
                                </div>
                            </form>
                        )}

                        {step === 'confirm' && (
                            <div>
                                <dl className="mb-6 divide-y divide-gray-200 border-t border-gray-200">
                                    <div className="grid grid-cols-3 gap-4 py-3 sm:grid-cols-4">
                                        <dt className="text-sm font-medium text-gray-500">
                                            {t('contact.fields.type')}
                                        </dt>
                                        <dd className="col-span-2 text-sm text-gray-900 sm:col-span-3">
                                            {t(
                                                `contact.types.${selectedType.labelKey}`,
                                            )}
                                        </dd>
                                    </div>
                                    <div className="grid grid-cols-3 gap-4 py-3 sm:grid-cols-4">
                                        <dt className="text-sm font-medium text-gray-500">
                                            {t('contact.fields.name')}
                                        </dt>
                                        <dd className="col-span-2 text-sm text-gray-900 sm:col-span-3">
                                            {data.name}
                                        </dd>
                                    </div>
                                    <div className="grid grid-cols-3 gap-4 py-3 sm:grid-cols-4">
                                        <dt className="text-sm font-medium text-gray-500">
                                            {t('contact.fields.email')}
                                        </dt>
                                        <dd className="col-span-2 text-sm text-gray-900 sm:col-span-3">
                                            {data.email}
                                        </dd>
                                    </div>
                                    <div className="grid grid-cols-3 gap-4 py-3 sm:grid-cols-4">
                                        <dt className="text-sm font-medium text-gray-500">
                                            {t('contact.fields.phone')}
                                        </dt>
                                        <dd className="col-span-2 text-sm text-gray-900 sm:col-span-3">
                                            {data.phone}
                                        </dd>
                                    </div>
                                    <div className="grid grid-cols-3 gap-4 py-3 sm:grid-cols-4">
                                        <dt className="text-sm font-medium text-gray-500">
                                            {t('contact.fields.message')}
                                        </dt>
                                        <dd className="col-span-2 whitespace-pre-wrap text-sm text-gray-900 sm:col-span-3">
                                            {data.message}
                                        </dd>
                                    </div>
                                </dl>

                                <div className="flex justify-end gap-3">
                                    <SecondaryButton
                                        onClick={() => setStep('input')}
                                        disabled={processing}
                                    >
                                        {t('contact.back')}
                                    </SecondaryButton>
                                    <PrimaryButton
                                        onClick={submit}
                                        disabled={processing}
                                    >
                                        {processing
                                            ? t('contact.submitting')
                                            : t('contact.submit')}
                                    </PrimaryButton>
                                </div>
                            </div>
                        )}

                        {step === 'complete' && (
                            <div className="py-8 text-center">
                                <h2 className="mb-4 text-lg font-semibold text-gray-900">
                                    {t('contact.completeTitle')}
                                </h2>
                                <p className="mb-6 text-sm leading-relaxed text-gray-700">
                                    {t('contact.completeMessage')}
                                </p>
                                <Link
                                    href={route('shops.index')}
                                    className="text-sm text-gray-600 underline hover:text-gray-900"
                                >
                                    {t('staticPages.backToHome')}
                                </Link>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            <Footer />
        </div>
    );
}
