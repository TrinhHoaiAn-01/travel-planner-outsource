type Disclosure = {
    title: string;
    rows?: { label: string; value: string; note?: string }[];
    footnotes?: string[];
};

export default function DisclosureCard({ payload }: { payload: Disclosure }) {
    return <section className="al-reveal rounded-(--radius-lg) border border-(--line) bg-(--card) p-5">
        <h3 className="al-display text-xl font-semibold">{payload.title}</h3>
        <dl className="mt-3 divide-y divide-(--line)">{payload.rows?.map((row, index) => <div key={index} className="py-2">
            <dt className="text-sm text-(--ink-soft)">{row.label}</dt><dd className="font-medium">{row.value}</dd>
            {row.note ? <p className="text-sm">{row.note}</p> : null}
        </div>)}</dl>
        {payload.footnotes?.map((note, index) => <p key={index} className="mt-2 text-xs text-(--ink-soft)">{note}</p>)}
    </section>;
}
