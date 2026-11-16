import { useEffect, useState } from 'react';
import type { FormEvent } from 'react';
import { useAuth } from '../auth/AuthContext';
import { askAssistant, getAssistantSuggestions } from '../../services/assistant';
import { resolveApiError } from '../../services/errors';

interface Message {
  role: 'user' | 'assistant';
  text: string;
  status?: string;
}

export function AssistantPage() {
  const { hasPermission } = useAuth();
  const [messages, setMessages] = useState<Message[]>([]);
  const [suggestions, setSuggestions] = useState<string[]>([]);
  const [question, setQuestion] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    getAssistantSuggestions()
      .then(setSuggestions)
      .catch(() => setSuggestions([]));
  }, []);

  if (!hasPermission('assistant.use')) {
    return (
      <section className="home">
        <p className="lead">No tiene permiso para usar el asistente de inventario.</p>
      </section>
    );
  }

  const send = async (text: string) => {
    const trimmed = text.trim();

    if (trimmed === '' || loading) {
      return;
    }

    setMessages((current) => [...current, { role: 'user', text: trimmed }]);
    setQuestion('');
    setLoading(true);
    setError(null);

    try {
      const response = await askAssistant(trimmed);
      setMessages((current) => [
        ...current,
        { role: 'assistant', text: response.answer, status: response.status },
      ]);
    } catch (requestError) {
      setError(resolveApiError(requestError).message);
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    void send(question);
  };

  return (
    <section className="users-page catalog-page">
      <header className="users-header">
        <div>
          <p className="eyebrow">Asistente</p>
          <h1>Consulta de inventario</h1>
          <p className="lead">
            Solo lectura. Las respuestas provienen de los datos de la empresa activa y sus permisos.
          </p>
        </div>
      </header>

      <div className="catalog-section">
        <p className="hint">
          Puede preguntar por stock, agotados, mínimos, salidas o un resumen. No puede modificar inventario.
        </p>

        {suggestions.length > 0 && messages.length === 0 && (
          <div className="users-filters">
            {suggestions.map((suggestion) => (
              <button
                key={suggestion}
                type="button"
                className="button-secondary"
                onClick={() => {
                  void send(suggestion);
                }}
              >
                {suggestion}
              </button>
            ))}
          </div>
        )}

        <div className="chat-log" aria-live="polite">
          {messages.map((message, index) => (
            <article
              key={`${message.role}-${index}`}
              className={message.role === 'user' ? 'chat-message is-user' : 'chat-message'}
            >
              <strong>{message.role === 'user' ? 'Usted' : 'Asistente'}</strong>
              <p>{message.text}</p>
              {message.status && message.status !== 'ok' && (
                <span className="hint">Estado: {message.status}</span>
              )}
            </article>
          ))}
          {loading && <p className="loading">Consultando…</p>}
        </div>

        {error && (
          <p className="form-error" role="alert">
            {error}
          </p>
        )}

        <form className="auth-form" onSubmit={handleSubmit}>
          <label className="field">
            <span>Pregunta</span>
            <input
              value={question}
              onChange={(event) => setQuestion(event.target.value)}
              maxLength={500}
              placeholder="Ej.: ¿qué productos están agotados?"
              disabled={loading}
            />
          </label>
          <button type="submit" className="button-primary" disabled={loading || question.trim() === ''}>
            {loading ? 'Consultando…' : 'Preguntar'}
          </button>
        </form>
      </div>
    </section>
  );
}
