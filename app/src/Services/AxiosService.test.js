import axios from 'axios';

import AxiosService from './AxiosService';

jest.mock('axios', () => ({
    get: jest.fn(),
    post: jest.fn(),
}));
jest.mock('Services/AlertService', () => ({
    showAlert: jest.fn(),
}));

describe('AxiosService', () => {
    beforeEach(() => {
        jest.clearAllMocks();
        window.apiInternalAccessNonce = undefined;
    });

    test('reads the current REST nonce when sending a request', async () => {
        const service = new AxiosService();
        window.apiInternalAccessNonce = 'fresh-nonce';
        axios.get.mockResolvedValue({data: {}});

        await service.getRequest('/test');

        expect(axios.get).toHaveBeenCalledWith(
            '/test',
            expect.objectContaining({
                headers: expect.objectContaining({
                    'X-WP-Nonce': 'fresh-nonce',
                }),
            }),
        );
    });

    test('rejects when the HTTP request fails', async () => {
        const service = new AxiosService();
        const error = {
            response: {
                data: {
                    data: {
                        alert: {
                            type: 'error',
                            message: 'Import selhal.',
                        },
                    },
                },
            },
        };
        axios.post.mockRejectedValue(error);

        await expect(service.postRequest('/test', {})).rejects.toBe(error);
    });
});
