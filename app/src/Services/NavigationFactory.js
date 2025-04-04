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
import {LicenceHelper} from "Helpers/LicenceHelper";

const Overview = lazy(() => import('Components/Content/Overview/Overview'));
const Statistics = lazy(() => (
	LicenceHelper.hasFmLicence()
	? import('Components/Content/Overview/Statistics')
	: import('Components/Content/NoFmLicence')
));
const Levels = lazy(() => import('Components/Content/Levels/Levels'));
const Common = lazy(() => import('Components/Content/Levels/Common'));
const Elements = lazy(() => import('Components/Content/Levels/Elements'));
const Members = lazy(() => import('Components/Content/Members/Members'));
const CreateMember = lazy(() => import('Components/Content/Members/CreateMember'));
const Connection = lazy(() => import('Components/Content/Connection/Connection'));
const SimpleShopToFAPIMember = lazy(() => import('Components/Content/Connection/SimpleShopToFAPIMember'));

export class NavigationFactory {
	create() {
		const navigation = new Navigation([
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
					new SubNavItem(
						SubNavItemType.STATISTICS,
						'Statistiky',
						Statistics,
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
					new SubNavItem(
						SubNavItemType.CREATE_MEMBER,
						'Vytvořit',
						CreateMember,
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

		if (LicenceHelper.isSimpleShopToFAPIMember()) {
			navigation.getNavItem(NavItemType.CONNECTION).getSubNavItems().push(
				new SubNavItem(
					SubNavItemType.SIMPLE_SHOP_TO_FAPI_MEMBER,
					'Přechod SimpleShop -> FAPI Member',
					SimpleShopToFAPIMember,
				)
			);
		}

		return navigation
	}
}
