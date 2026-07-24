import MembershipClient from "Clients/MembershipClient";
import Papa from "papaparse";

const IMPORT_BATCH_SIZE = 100;

export class MemberService {
    static membershipClient = new MembershipClient();

    static async retryAsync(fn, retries = 10, delay = 500) {
        let lastError;
        for (let attempt = 1; attempt <= retries; attempt++) {
            try {
                return await fn();
            } catch (error) {
                lastError = error;
                console.warn(`Attempt ${attempt} failed. Retrying...`);
                await new Promise(resolve => setTimeout(resolve, delay));
            }
        }
        throw lastError;
    }

    static async exportCsv(members) {
        let membersData = [];

        // Místo Promise.all sekvenční for loop + retry
        for (const member of members) {
            let memberships = await this.retryAsync(() =>
                    this.membershipClient.getAllForUser(member.id),
                10,
                500
            );

            memberships.map((membership) => {
                membersData.push({
                    email: member.email,
                    first_name: member.firstName,
                    last_name: member.lastName,
                    level: membership.levelId,
                    registered: membership.registered?.getDateTime(),
                    until: membership.until?.getDate(),
                });
            });
        }

        membersData = Papa.unparse(membersData)

        const blob = new Blob([membersData], {type: 'text/csv;charset=utf-8;'});
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.setAttribute('download', 'fm_members.csv');

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    static async importCsv(csv) {
        const results = Papa.parse(csv, {
            header: true,
            skipEmptyLines: true,
        });

        for (let offset = 0; offset < results.data.length; offset += IMPORT_BATCH_SIZE) {
            await this.membershipClient.createMultiple(
                results.data.slice(offset, offset + IMPORT_BATCH_SIZE),
            );
        }
    }
}
