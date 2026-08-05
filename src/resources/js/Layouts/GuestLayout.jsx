import ApplicationLogo from '@/Components/ApplicationLogo';
import LocaleSwitcher from '@/Components/LocaleSwitcher';
import { Link, usePage } from '@inertiajs/react';

export default function GuestLayout({ children }) {
    const { siteLocales } = usePage().props;

    return (
        <div className="min-h-screen bg-gray-100">
            <header className="bg-white shadow-sm">
                <div className="mx-auto flex h-20 max-w-5xl items-center justify-between px-6">
                    <Link href="/">
                        <ApplicationLogo className="h-12 w-12 fill-current text-gray-500" />
                    </Link>
                    <LocaleSwitcher available={siteLocales} />
                </div>
            </header>

            <div className="flex justify-center px-6 py-10">
                <div className="w-full max-w-md overflow-hidden bg-white px-6 py-4 shadow-md sm:rounded-lg">
                    {children}
                </div>
            </div>
        </div>
    );
}
