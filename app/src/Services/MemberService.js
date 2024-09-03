import MembershipClient from "Clients/MembershipClient";
import MemberSectionClient from "Clients/MemberSectionClient";

export class MemberService {
	static membershipClient = new MembershipClient();
	static memberSectionClient = new MemberSectionClient();

	static convertToCsv(data) {
		const headers = Object.keys(data[0]).join(',');
		const rows = data.map(row =>
			Object.values(row).join(',')
		).join('\n');

		return `${headers}\n${rows}`;
	}

	static async exportCsv(members) {
		var membersData = await Promise.all(members.map(async (member) => {
			var memberships = await this.membershipClient.getAllForUser(member.id);

			return memberships.map((membership) => {
				return {
					email: member.email,
					first_name: member.firstName,
					last_name: member.lastName,
					level: membership.levelId,
					registered: membership.registered?.getDateTime(),
					until: membership.until?.getDate(),
				}
			})
		}));

		membersData = this.convertToCsv(membersData.flat(1));

		const blob = new Blob([membersData], { type: 'text/csv;charset=utf-8;' });
		const link = document.createElement('a');
		link.href = URL.createObjectURL(blob);
		link.setAttribute('download', 'fm_members');

		document.body.appendChild(link);
		link.click();
		document.body.removeChild(link);
	}

	static async importCsv(csv) {
		const rows = csv.split('\n');
    	const headers = rows[0].split(',');

		const data = rows.slice(1).map(row => {
			const values = row.split(',');
			const obj = {};

			headers.forEach((header, index) => {
				obj[header?.trim()] = values[index]?.trim();
			});

			return obj;
		});

		await this.membershipClient.createMultiple(data);

		return null;
	}
}
