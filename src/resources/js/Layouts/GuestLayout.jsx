import ApplicationLogo from '@/Components/ApplicationLogo';
import Footer from '@/Components/Footer';
import LocaleSwitcher from '@/Components/LocaleSwitcher';
import { Link, usePage } from '@inertiajs/react';

export default function GuestLayout({ children, footer = true }) {
    const { siteLocales } = usePage().props;

    return (
        <div className="flex min-h-screen flex-col bg-gray-100">
            <header className="bg-white shadow-sm">
                <div className="mx-auto flex h-20 max-w-5xl items-center justify-between px-6">
                    <Link href="/">
                        <ApplicationLogo className="h-12 w-12 fill-current text-gray-500" />
                    </Link>
                    <LocaleSwitcher available={siteLocales} />
                </div>
            </header>

            <div className="flex flex-1 justify-center px-6 py-10">
                <div className="w-full max-w-md overflow-hidden bg-white px-6 py-4 shadow-md sm:rounded-lg">
                    {children}
                </div>
            </div>

            {footer && <Footer />}
        </div>
    );
}
