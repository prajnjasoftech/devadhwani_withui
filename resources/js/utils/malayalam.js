// Malayalam month names mapping
export const malayalamMonths = {
    'Chingam': 'ചിങ്ങം',
    'Kanni': 'കന്നി',
    'Thulam': 'തുലാം',
    'Vrischikam': 'വൃശ്ചികം',
    'Dhanu': 'ധനു',
    'Makaram': 'മകരം',
    'Kumbham': 'കുംഭം',
    'Meenam': 'മീനം',
    'Medam': 'മേടം',
    'Edavam': 'ഇടവം',
    'Mithunam': 'മിഥുനം',
    'Karkidakam': 'കർക്കിടകം',
};

// Malayalam nakshatra names mapping (27 nakshatras)
export const malayalamNakshatras = {
    'Ashwini': 'അശ്വതി',
    'Bharani': 'ഭരണി',
    'Krittika': 'കാർത്തിക',
    'Rohini': 'രോഹിണി',
    'Mrigashirsha': 'മകയിരം',
    'Ardra': 'തിരുവാതിര',
    'Punarvasu': 'പുണർതം',
    'Pushya': 'പൂയം',
    'Ashlesha': 'ആയില്യം',
    'Magha': 'മകം',
    'Purva Phalguni': 'പൂരം',
    'Uttara Phalguni': 'ഉത്രം',
    'Hasta': 'അത്തം',
    'Chitra': 'ചിത്തിര',
    'Swati': 'ചോതി',
    'Vishakha': 'വിശാഖം',
    'Anuradha': 'അനിഴം',
    'Jyeshtha': 'തൃക്കേട്ട',
    'Mula': 'മൂലം',
    'Moola': 'മൂലം',
    'Purva Ashadha': 'പൂരാടം',
    'Uttara Ashadha': 'ഉത്രാടം',
    'Shravana': 'തിരുവോണം',
    'Dhanishta': 'അവിട്ടം',
    'Shatabhisha': 'ചതയം',
    'Purva Bhadrapada': 'പൂരുരുട്ടാതി',
    'Uttara Bhadrapada': 'ഉത്രട്ടാതി',
    'Revati': 'രേവതി',
};

// Nakshatra list with both English and Malayalam
export const nakshatraList = [
    { value: 'Ashwini', label: 'അശ്വതി (Ashwini)' },
    { value: 'Bharani', label: 'ഭരണി (Bharani)' },
    { value: 'Krittika', label: 'കാർത്തിക (Krittika)' },
    { value: 'Rohini', label: 'രോഹിണി (Rohini)' },
    { value: 'Mrigashirsha', label: 'മകയിരം (Mrigashirsha)' },
    { value: 'Ardra', label: 'തിരുവാതിര (Ardra)' },
    { value: 'Punarvasu', label: 'പുണർതം (Punarvasu)' },
    { value: 'Pushya', label: 'പൂയം (Pushya)' },
    { value: 'Ashlesha', label: 'ആയില്യം (Ashlesha)' },
    { value: 'Magha', label: 'മകം (Magha)' },
    { value: 'Purva Phalguni', label: 'പൂരം (Purva Phalguni)' },
    { value: 'Uttara Phalguni', label: 'ഉത്രം (Uttara Phalguni)' },
    { value: 'Hasta', label: 'അത്തം (Hasta)' },
    { value: 'Chitra', label: 'ചിത്തിര (Chitra)' },
    { value: 'Swati', label: 'ചോതി (Swati)' },
    { value: 'Vishakha', label: 'വിശാഖം (Vishakha)' },
    { value: 'Anuradha', label: 'അനിഴം (Anuradha)' },
    { value: 'Jyeshtha', label: 'തൃക്കേട്ട (Jyeshtha)' },
    { value: 'Mula', label: 'മൂലം (Mula)' },
    { value: 'Purva Ashadha', label: 'പൂരാടം (Purva Ashadha)' },
    { value: 'Uttara Ashadha', label: 'ഉത്രാടം (Uttara Ashadha)' },
    { value: 'Shravana', label: 'തിരുവോണം (Shravana)' },
    { value: 'Dhanishta', label: 'അവിട്ടം (Dhanishta)' },
    { value: 'Shatabhisha', label: 'ചതയം (Shatabhisha)' },
    { value: 'Purva Bhadrapada', label: 'പൂരുരുട്ടാതി (Purva Bhadrapada)' },
    { value: 'Uttara Bhadrapada', label: 'ഉത്രട്ടാതി (Uttara Bhadrapada)' },
    { value: 'Revati', label: 'രേവതി (Revati)' },
];

// Convert English month name to Malayalam
export const toMalayalamMonth = (dateStr) => {
    if (!dateStr) return '';
    for (const [eng, mal] of Object.entries(malayalamMonths)) {
        if (dateStr.includes(eng)) {
            return dateStr.replace(eng, mal);
        }
    }
    return dateStr;
};

// Convert English nakshatra name to Malayalam
export const toMalayalamNakshatra = (nakshatraStr) => {
    if (!nakshatraStr) return '';
    return malayalamNakshatras[nakshatraStr] || nakshatraStr;
};
