import { useState } from 'react';
import axios from '../../axios';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';

export default function useRegisterForm() {
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [passwordConfirmation, setPasswordConfirmation] = useState('');
    const [errors, setErrors] = useState({});
    const navigate = useNavigate();
    const { setUser } = useAuth();

    const handleSubmit = async (e) => {
        e.preventDefault();
        setErrors({});

        try {
            await axios.get('/sanctum/csrf-cookie');
            await axios.post('/api/register', {
                name,
                email,
                password,
                password_confirmation: passwordConfirmation,
            });

            localStorage.setItem('registerEmail', email);
            setUser(null);
            navigate('/email/verify/notice');
        } catch (err) {
            if (err.response?.status === 422) {
                setErrors(err.response.data.errors || {});
            } else {
                alert('サーバーエラーが発生しました');
            }
        }
    };

    return {
        name, setName,
        email, setEmail,
        password, setPassword,
        passwordConfirmation, setPasswordConfirmation,
        errors, handleSubmit,
    };
}
