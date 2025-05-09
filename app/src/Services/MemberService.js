import MembershipClient from "Clients/MembershipClient";
import Papa from "papaparse";

export class MemberService {
    static membershipClient = new MembershipClient();

    static async exportCsv(members) {
        let membersData = [];

        await Promise.all(
            members.map(
                async (member) => {
                    let memberships = await this.membershipClient.getAllForUser(member.id);

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
            )
        );

        membersData = Papa.unparse(membersData)

        const blob = new Blob([membersData], {type: 'text/csv;charset=utf-8;'});
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.setAttribute('download', 'fm_members.csv');

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    static importCsv(csv) {
        return new Promise((resolve, reject) => {
            Papa.parse(csv, {
                header: true,
                worker: false,
                step: (results) => {
                    this.membershipClient.create(results.data);
                },
                complete: () => {
                    return resolve();
                },
                error: (error) => {
                    console.log(error)
                    return reject(error);
                }
            });
        });
    }
}
