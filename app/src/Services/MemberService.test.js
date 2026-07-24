import {MemberService} from './MemberService';

jest.mock('axios', () => ({
    get: jest.fn(),
    post: jest.fn(),
}));

describe('MemberService.importCsv', () => {
    test('submits non-empty CSV rows in one awaited bulk request', async () => {
        let finishImport;
        const createMultiple = jest.fn(() => new Promise((resolve) => {
            finishImport = resolve;
        }));
        MemberService.membershipClient = {
            create: jest.fn(),
            createMultiple,
        };

        let importFinished = false;
        const importPromise = MemberService.importCsv(
            'email,level,send_email\nfirst@example.test,4,false\nsecond@example.test,5,false\n',
        ).then(() => {
            importFinished = true;
        });

        await Promise.resolve();

        expect(createMultiple).toHaveBeenCalledTimes(1);
        expect(createMultiple).toHaveBeenCalledWith([
            {email: 'first@example.test', level: '4', send_email: 'false'},
            {email: 'second@example.test', level: '5', send_email: 'false'},
        ]);
        expect(importFinished).toBe(false);

        finishImport();
        await importPromise;

        expect(importFinished).toBe(true);
    });

    test('submits 532 CSV rows sequentially in batches of at most 100 rows', async () => {
        const finishBatch = [];
        const createMultiple = jest.fn(() => new Promise((resolve) => {
            finishBatch.push(resolve);
        }));
        MemberService.membershipClient = {
            create: jest.fn(),
            createMultiple,
        };
        const rows = Array.from(
            {length: 532},
            (_, index) => `member-${index + 1}@example.test,4,false`,
        );

        const importPromise = MemberService.importCsv(
            `email,level,send_email\n${rows.join('\n')}\n`,
        );

        await Promise.resolve();

        expect(createMultiple).toHaveBeenCalledTimes(1);
        expect(createMultiple.mock.calls[0][0]).toHaveLength(100);
        expect(createMultiple.mock.calls[0][0][0].email).toBe('member-1@example.test');
        expect(createMultiple.mock.calls[0][0][99].email).toBe('member-100@example.test');

        for (let batch = 0; batch < 5; batch++) {
            finishBatch[batch]();
            await Promise.resolve();
            await Promise.resolve();

            expect(createMultiple).toHaveBeenCalledTimes(batch + 2);
        }

        expect(createMultiple.mock.calls.map(([members]) => members.length))
            .toEqual([100, 100, 100, 100, 100, 32]);
        expect(createMultiple.mock.calls[5][0][0].email).toBe('member-501@example.test');
        expect(createMultiple.mock.calls[5][0][31].email).toBe('member-532@example.test');

        finishBatch[5]();
        await importPromise;
    });
});
