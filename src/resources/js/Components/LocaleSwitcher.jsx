import { router, usePage } from '@inertiajs/react';
import { useId } from 'react';
import { useTranslation } from 'react-i18next';

const STAR_POINTS =
    '0,-1 0.2245,-0.309 0.951,-0.309 0.3633,0.118 0.588,0.809 0,0.382 -0.588,0.809 -0.3633,0.118 -0.951,-0.309 -0.2245,-0.309';

function Star({ cx, cy, r, rotate = 0, fill }) {
    return (
        <polygon
            points={STAR_POINTS}
            fill={fill}
            transform={`translate(${cx},${cy}) scale(${r}) rotate(${rotate})`}
        />
    );
}

function JapanFlag(props) {
    return (
        <svg viewBox="0 0 60 40" {...props}>
            <rect width="60" height="40" fill="#fff" />
            <circle cx="30" cy="20" r="12" fill="#BC002D" />
        </svg>
    );
}

function UkFlag(props) {
    const clipId = useId();
    return (
        <svg viewBox="0 0 60 40" {...props}>
            <clipPath id={clipId}>
                <rect width="60" height="40" />
            </clipPath>
            <g clipPath={`url(#${clipId})`}>
                <rect width="60" height="40" fill="#012169" />
                <path d="M0,0 60,40 M60,0 0,40" stroke="#fff" strokeWidth="8" />
                <path d="M0,0 60,40 M60,0 0,40" stroke="#C8102E" strokeWidth="4" />
                <path d="M30,0 30,40 M0,20 60,20" stroke="#fff" strokeWidth="14" />
                <path d="M30,0 30,40 M0,20 60,20" stroke="#C8102E" strokeWidth="8" />
            </g>
        </svg>
    );
}

function ChinaFlag(props) {
    return (
        <svg viewBox="0 0 60 40" {...props}>
            <rect width="60" height="40" fill="#DE2910" />
            <Star cx={10} cy={8} r={6} fill="#FFDE00" />
            <Star cx={20} cy={4} r={2} rotate={-121} fill="#FFDE00" />
            <Star cx={24} cy={7} r={2} rotate={-98} fill="#FFDE00" />
            <Star cx={24} cy={13} r={2} rotate={-74} fill="#FFDE00" />
            <Star cx={20} cy={16} r={2} rotate={-51} fill="#FFDE00" />
        </svg>
    );
}

function KoreaFlag(props) {
    function Trigram({ x, y, pattern }) {
        return (
            <g transform={`translate(${x},${y}) rotate(-56)`} fill="#000">
                {pattern.map((broken, i) =>
                    broken ? (
                        <g key={i}>
                            <rect
                                x={-4}
                                y={i * 2.4 - 2.6}
                                width={3}
                                height={1}
                            />
                            <rect x={1} y={i * 2.4 - 2.6} width={3} height={1} />
                        </g>
                    ) : (
                        <rect
                            key={i}
                            x={-4}
                            y={i * 2.4 - 2.6}
                            width={8}
                            height={1}
                        />
                    ),
                )}
            </g>
        );
    }

    return (
        <svg viewBox="0 0 60 40" {...props}>
            <rect width="60" height="40" fill="#fff" />
            <g transform="translate(30,20)">
                <circle r="8" fill="#fff" />
                <path
                    d="M0,-8 A4,4 0 0,1 0,0 A4,4 0 0,0 0,8 A8,8 0 0,1 0,-8 Z"
                    fill="#C60C30"
                />
                <path
                    d="M0,8 A4,4 0 0,1 0,0 A4,4 0 0,0 0,-8 A8,8 0 0,1 0,8 Z"
                    fill="#003478"
                />
            </g>
            <Trigram x={12} y={8} pattern={[false, false, false]} />
            <Trigram x={48} y={8} pattern={[true, false, true]} />
            <Trigram x={12} y={32} pattern={[false, true, false]} />
            <Trigram x={48} y={32} pattern={[true, true, true]} />
        </svg>
    );
}

function ThailandFlag(props) {
    return (
        <svg viewBox="0 0 60 40" {...props}>
            <rect width="60" height="40" fill="#A51931" />
            <rect y="6.67" width="60" height="26.67" fill="#fff" />
            <rect y="13.33" width="60" height="13.33" fill="#2D2A4A" />
        </svg>
    );
}

function MyanmarFlag(props) {
    return (
        <svg viewBox="0 0 60 40" {...props}>
            <rect width="60" height="13.33" fill="#FECB00" />
            <rect y="13.33" width="60" height="13.33" fill="#34B233" />
            <rect y="26.67" width="60" height="13.33" fill="#EA2839" />
            <Star cx={30} cy={20} r={9} fill="#fff" />
        </svg>
    );
}

function VietnamFlag(props) {
    return (
        <svg viewBox="0 0 60 40" {...props}>
            <rect width="60" height="40" fill="#DA251D" />
            <Star cx={30} cy={20} r={9} fill="#FFFF00" />
        </svg>
    );
}

function PhilippinesFlag(props) {
    return (
        <svg viewBox="0 0 60 40" {...props}>
            <rect width="60" height="20" fill="#0038A8" />
            <rect y="20" width="60" height="20" fill="#CE1126" />
            <polygon points="0,0 26,20 0,40" fill="#fff" />
            <Star cx={7} cy={20} r={3} fill="#FCD116" />
            <Star cx={17} cy={7} r={2} fill="#FCD116" />
            <Star cx={17} cy={33} r={2} fill="#FCD116" />
        </svg>
    );
}

function BrazilFlag(props) {
    return (
        <svg viewBox="0 0 60 40" {...props}>
            <rect width="60" height="40" fill="#009739" />
            <polygon points="30,4 56,20 30,36 4,20" fill="#FEDD00" />
            <circle cx="30" cy="20" r="9" fill="#012169" />
        </svg>
    );
}

function NepalFlag(props) {
    return (
        <svg viewBox="0 0 60 40" {...props}>
            <rect width="60" height="40" fill="#fff" />
            <rect x="2" y="2" width="56" height="36" fill="#DC143C" rx="2" />
            <circle cx="30" cy="14" r="4" fill="#fff" />
            <Star cx={30} cy={27} r={5} fill="#fff" />
        </svg>
    );
}

function IndonesiaFlag(props) {
    return (
        <svg viewBox="0 0 60 40" {...props}>
            <rect width="60" height="20" fill="#CE1126" />
            <rect y="20" width="60" height="20" fill="#fff" />
        </svg>
    );
}

function TaiwanFlag(props) {
    return (
        <svg viewBox="0 0 60 40" {...props}>
            <rect width="60" height="40" fill="#FE0000" />
            <rect width="30" height="20" fill="#000095" />
            <Star cx={15} cy={10} r={6} fill="#fff" />
        </svg>
    );
}

function IndiaFlag(props) {
    return (
        <svg viewBox="0 0 60 40" {...props}>
            <rect width="60" height="13.33" fill="#FF9933" />
            <rect y="13.33" width="60" height="13.33" fill="#fff" />
            <rect y="26.67" width="60" height="13.33" fill="#138808" />
            <circle
                cx="30"
                cy="20"
                r="5"
                fill="none"
                stroke="#000080"
                strokeWidth="0.8"
            />
            <circle cx="30" cy="20" r="0.8" fill="#000080" />
        </svg>
    );
}

function PeruFlag(props) {
    return (
        <svg viewBox="0 0 60 40" {...props}>
            <rect width="20" height="40" fill="#D91023" />
            <rect x="20" width="20" height="40" fill="#fff" />
            <rect x="40" width="20" height="40" fill="#D91023" />
        </svg>
    );
}

function SriLankaFlag(props) {
    return (
        <svg viewBox="0 0 60 40" {...props}>
            <rect width="60" height="40" fill="#8D153A" />
            <rect x="8" width="8" height="40" fill="#00534E" />
            <rect x="16" width="8" height="40" fill="#FF7300" />
            <rect x="24" width="36" height="40" fill="#8D153A" />
            <rect
                x="24"
                y="4"
                width="32"
                height="32"
                rx="2"
                fill="none"
                stroke="#FFB700"
                strokeWidth="1.2"
            />
        </svg>
    );
}

function CambodiaFlag(props) {
    return (
        <svg viewBox="0 0 60 40" {...props}>
            <rect width="60" height="10" fill="#032EA1" />
            <rect y="10" width="60" height="20" fill="#E00025" />
            <rect y="30" width="60" height="10" fill="#032EA1" />
            <rect x="22" y="13" width="16" height="14" fill="#fff" />
        </svg>
    );
}

function BangladeshFlag(props) {
    return (
        <svg viewBox="0 0 60 40" {...props}>
            <rect width="60" height="40" fill="#006A4E" />
            <circle cx="26" cy="20" r="10" fill="#F42A41" />
        </svg>
    );
}

function FranceFlag(props) {
    return (
        <svg viewBox="0 0 60 40" {...props}>
            <rect width="20" height="40" fill="#0055A4" />
            <rect x="20" width="20" height="40" fill="#fff" />
            <rect x="40" width="20" height="40" fill="#EF4135" />
        </svg>
    );
}

function MongoliaFlag(props) {
    return (
        <svg viewBox="0 0 60 40" {...props}>
            <rect width="15" height="40" fill="#DA2032" />
            <rect x="15" width="30" height="40" fill="#015197" />
            <rect x="45" width="15" height="40" fill="#DA2032" />
            <circle cx="7.5" cy="12" r="2.5" fill="#F9CF02" />
        </svg>
    );
}

function PakistanFlag(props) {
    return (
        <svg viewBox="0 0 60 40" {...props}>
            <rect width="60" height="40" fill="#01411C" />
            <rect width="15" height="40" fill="#fff" />
            <path
                d="M40,10 A10,10 0 1,0 40,30 A8,8 0 1,1 40,10 Z"
                fill="#fff"
            />
            <Star cx={48} cy={13} r={3.5} rotate={-20} fill="#fff" />
        </svg>
    );
}

const LOCALES = [
    { code: 'ja', Flag: JapanFlag, labelKey: 'lang.japanese' },
    { code: 'en', Flag: UkFlag, labelKey: 'lang.english' },
    { code: 'zh', Flag: ChinaFlag, labelKey: 'lang.chinese' },
    { code: 'ko', Flag: KoreaFlag, labelKey: 'lang.korean' },
    { code: 'th', Flag: ThailandFlag, labelKey: 'lang.thai' },
    { code: 'my', Flag: MyanmarFlag, labelKey: 'lang.myanmar' },
    { code: 'vi', Flag: VietnamFlag, labelKey: 'lang.vietnamese' },
    { code: 'tl', Flag: PhilippinesFlag, labelKey: 'lang.filipino' },
    { code: 'pt', Flag: BrazilFlag, labelKey: 'lang.portuguese' },
    { code: 'ne', Flag: NepalFlag, labelKey: 'lang.nepali' },
    { code: 'id', Flag: IndonesiaFlag, labelKey: 'lang.indonesian' },
    { code: 'zh-TW', Flag: TaiwanFlag, labelKey: 'lang.taiwanese' },
    { code: 'hi', Flag: IndiaFlag, labelKey: 'lang.hindi' },
    { code: 'es', Flag: PeruFlag, labelKey: 'lang.spanish' },
    { code: 'si', Flag: SriLankaFlag, labelKey: 'lang.sinhala' },
    { code: 'km', Flag: CambodiaFlag, labelKey: 'lang.khmer' },
    { code: 'bn', Flag: BangladeshFlag, labelKey: 'lang.bengali' },
    { code: 'fr', Flag: FranceFlag, labelKey: 'lang.french' },
    { code: 'mn', Flag: MongoliaFlag, labelKey: 'lang.mongolian' },
    { code: 'ur', Flag: PakistanFlag, labelKey: 'lang.urdu' },
];

export default function LocaleSwitcher({ available }) {
    const { t } = useTranslation();
    const { locale } = usePage().props;

    const switchTo = (value) => {
        if (value === locale) {
            return;
        }

        router.post(
            route('locale.update'),
            { locale: value },
            { preserveScroll: true },
        );
    };

    const flagClass = (active) =>
        'h-4 w-6 rounded-sm ring-1 ring-black/10 transition ' +
        (active ? 'opacity-100' : 'opacity-40 hover:opacity-70');

    const visible = available
        ? LOCALES.filter((l) => available.includes(l.code))
        : LOCALES;

    return (
        <div className="flex items-center gap-2">
            {visible.map(({ code, Flag, labelKey }) => (
                <button
                    key={code}
                    type="button"
                    onClick={() => switchTo(code)}
                    aria-label={t(labelKey)}
                    aria-pressed={locale === code}
                >
                    <Flag className={flagClass(locale === code)} />
                </button>
            ))}
        </div>
    );
}
