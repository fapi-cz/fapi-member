import MemberLevel from './MemberLevel'

export default class MemberSection extends MemberLevel
{
	levels = [];

	constructor(data) {
		super(data);
		data?.levels.forEach((level) => {
			this.levels.push(new MemberLevel(level));
		});
	}
}
