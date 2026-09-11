import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import en from './locales/en.json';
import id from './locales/id.json';
import ja from './locales/ja.json';
import ko from './locales/ko.json';
import my from './locales/my.json';
import ne from './locales/ne.json';
import pt from './locales/pt.json';
import th from './locales/th.json';
import tl from './locales/tl.json';
import vi from './locales/vi.json';
import zh from './locales/zh.json';
import zhTW from './locales/zh-TW.json';

i18n.use(initReactI18next).init({
    resources: {
        ja: { translation: ja },
        en: { translation: en },
        zh: { translation: zh },
        ko: { translation: ko },
        th: { translation: th },
        my: { translation: my },
        vi: { translation: vi },
        tl: { translation: tl },
        pt: { translation: pt },
        ne: { translation: ne },
        id: { translation: id },
        'zh-TW': { translation: zhTW },
    },
    lng: document.documentElement.lang || 'ja',
    fallbackLng: 'ja',
    interpolation: {
        escapeValue: false,
    },
});

export default i18n;
