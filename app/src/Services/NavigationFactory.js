import React, {lazy, useState} from 'react';

import iconOverview from 'Images/overview.svg';
import iconLevels from 'Images/levels.svg';
import iconMembers from 'Images/member.svg';
import iconConnect from 'Images/connect.svg';

import { SubNavItemType } from "Enums/SubNavItemType";
import { NavItemType } from "Enums/NavItemType";
import { Navigation } from "Models/Navigation";
import { NavItem } from "Models/NavItem";
import { SubNavItem } from "Models/SubNavItem";

const Overview = lazy(() => import('Components/Content/Overview/Overview'));
const Levels = lazy(() => import('Components/Content/Levels/Levels'));
const Common = lazy(() => import('Components/Content/Levels/Common'));
const Elements = lazy(() => import('Components/Content/Levels/Elements'));
const Members = lazy(() => import('Components/Content/Members/Members'));
const Connection = lazy(() => import('Components/Content/Connection/Connection'));

export class NavigationFactory {
	create() {
		return new Navigation([
			new NavItem(
				NavItemType.OVERVIEW,
				'Přehled',
				iconOverview,
				[
					new SubNavItem(
						SubNavItemType.OVERVIEW,
						'Přehled',
						Overview,
					),
				]
			),
			new NavItem(
				NavItemType.LEVELS,
				'Členské Sekce',
				iconLevels,
				[
					new SubNavItem(
						SubNavItemType.LEVELS,
						'Sekce / úrovně',
						Levels,
					),
					new SubNavItem(
						SubNavItemType.COMMON,
						'Společné',
						Common,
					),
					new SubNavItem(
						SubNavItemType.ELEMENTS,
						'Prvky pro web',
						Elements,
					),
				],
			),
			new NavItem(
				NavItemType.MEMBERS,
				'Členové',
				iconMembers,
				[
					new SubNavItem(
						SubNavItemType.MEMBERS,
						'Členové',
						Members,
					),
				],
			),
			new NavItem(
				NavItemType.CONNECTION,
				'Propojení',
				iconConnect,
				[
					new SubNavItem(
						SubNavItemType.CONNECTION,
						'Propojení',
						Connection,
					),
				]
			),
		]);
	}
}
