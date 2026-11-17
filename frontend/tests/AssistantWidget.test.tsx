import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { AssistantWidget } from '../src/features/assistant/AssistantWidget';

const mocks = vi.hoisted(() => ({
  askAssistant: vi.fn(),
  getAssistantSuggestions: vi.fn(),
}));

vi.mock('../src/services/assistant', () => ({
  askAssistant: mocks.askAssistant,
  getAssistantSuggestions: mocks.getAssistantSuggestions,
}));

describe('AssistantWidget', () => {
  beforeEach(() => {
    mocks.getAssistantSuggestions.mockReset().mockResolvedValue([
      '¿Cuántos productos agotados hay?',
    ]);
    mocks.askAssistant.mockReset().mockResolvedValue({
      intent: 'summary',
      answer: 'Hay 1 producto agotado.',
      evidence: [],
      status: 'ok',
      read_only: true,
      generated_at: '2026-11-17T00:00:00Z',
    });
  });

  it('abre el panel, muestra sugerencias y responde', async () => {
    render(<AssistantWidget />);

    await userEvent.click(screen.getByRole('button', { name: 'Abrir asistente' }));

    expect(
      screen.getByRole('dialog', { name: 'Asistente de inventario' }),
    ).toBeInTheDocument();

    const suggestion = await screen.findByRole('button', {
      name: '¿Cuántos productos agotados hay?',
    });
    await userEvent.click(suggestion);

    expect(await screen.findByText('Hay 1 producto agotado.')).toBeInTheDocument();
  });

  it('cierra el panel con Escape', async () => {
    render(<AssistantWidget />);
    await userEvent.click(screen.getByRole('button', { name: 'Abrir asistente' }));
    await userEvent.keyboard('{Escape}');

    expect(
      screen.queryByRole('dialog', { name: 'Asistente de inventario' }),
    ).not.toBeInTheDocument();
  });
});
